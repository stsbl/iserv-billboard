<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Controller;

use IServ\BootstrapBundle\Form\Type\FormActionsType;
use IServ\CoreBundle\Event\NotificationEvent;
use IServ\CoreBundle\Traits\LoggerTrait;
use IServ\CrudBundle\Contracts\CrudContract;
use IServ\CrudBundle\Controller\StrictCrudController;
use IServ\FilesystemBundle\Upload\Form\Type\UniversalFileType;
use IServ\Library\Config\Config;
use IServ\Library\Flash\FlashInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stsbl\BillBoardBundle\Crud\EntryCrud;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Stsbl\BillBoardBundle\Form\DataTransformer\FileToUuidTransformer;
use Stsbl\BillBoardBundle\Image\ImageManager;
use Stsbl\BillBoardBundle\Image\ImageUpload;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 *
 * @Route("/billboard")
 */
final class EntryController extends StrictCrudController
{
    use CommentFormTrait;
    use LoggerInitializationTrait;
    use LoggerTrait;

    /**
     * {@inheritdoc}
     *
     * Overrides default addAction to pass some additional variables to the template
     *
     * @return Response|array
     */
    public function addAction(Request $request): array|Response
    {
        $ret = parent::addAction($request);

        if (is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     *
     * Overrides default editAction to pass some additional variables to the template
     *
     * @return Response|array
     */
    public function editAction(Request $request, $id): array|Response
    {
        $ret = parent::editAction($request, $id);

        if (is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     *
     * Overrides default showAction to pass some additional variables to the template
     *
     * @return Response|array
     */
    public function showAction(Request $request, $id): array|Response
    {
        if ($this->handleImageUploadForm($request, (int)$id) || $this->handleDeleteConfirmForm($request)) {
            return $this->redirectToRoute('billboard_show', ['id' => $id]);
        }

        $ret = parent::showAction($request, $id);
        /** @var Entry $entry */
        $entry = $ret['item'];
        /** @var EntryCrud $crud */
        $crud = $this->crud;

        if (is_array($ret)) {
            $ret['commentForm'] = $this->getCommentForm($entry)->createView();
            $ret['imageUploadForm'] = $this->getImageUploadForm($entry)->createView();
            $ret['imageDeleteConfirmForm'] = $this->getDeleteConfirmForm()->createView();
            $ret['commentsEnabled'] = $this->get(Config::class)->get('BillBoardEnableComments');
            $ret['moderator'] = $crud->isModerator();
            $ret['authorIsDeleted'] = !$entry->hasValidAuthor();
        }

        return $ret;
    }

    /**
     * Locks an opened entry
     *
     * @Route("/entry/lock/{id}", name="billboard_lock")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     */
    public function lockAction(Entry $entry): RedirectResponse
    {
        $entry->setClosed(true);

        $em = $this->getDoctrine()->getManagerForClass(Entry::class);

        $em->persist($entry);
        $em->flush();

        $this->notifyLock($entry);
        $this->log(sprintf(
            'Eintrag "%s" von %s für Schreibzugriffe gesperrt',
            $entry,
            $entry->getAuthorDisplay()
        ));
        $this->get(FlashInterface::class)->success(sprintf(_('Entry is now locked: %s'), $entry));

        return $this->redirect($this->generateUrl('billboard_show', ['id' => $entry->getId()]));
    }

    /**
     * Opens a locked entry
     *
     * @Route("/entry/unlock/{id}", name="billboard_unlock")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     */
    public function unlockAction(Entry $entry): RedirectResponse
    {
        $entry->setClosed(false);

        $em = $this->getDoctrine()->getManagerForClass(Entry::class);

        $em->persist($entry);
        $em->flush();

        $this->notifyOpen($entry);
        $this->log(sprintf(
            'Eintrag "%s" von %s für Schreibzugriffe geöffnet',
            $entry,
            $entry->getAuthorDisplay()
        ));
        $this->container->get(FlashInterface::class)->success(sprintf(_('Entry is now unlocked: %s'), $entry));

        return $this->redirect($this->generateUrl('billboard_show', ['id' => $entry->getId()]));
    }

    /**
     * Create form for image upload
     */
    private function getImageUploadForm(Entry $entry): FormInterface
    {
        $entryImage = new ImageUpload(EntryImage::createForEntryAndUser($entry, $this->getUser()));
        $builder = $this->createFormBuilder($entryImage);

        $builder
            ->add('image', UniversalFileType::class, [
                'label' => _('Image'),
                'multiple' => false,
            ])
            ->add('description', TextType::class, [
                'label' => _('Description'),
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => _('Upload'),
                'buttonClass' => 'btn-success',
                'icon' => 'pro-upload',
            ])
        ;


        return $builder->getForm();
    }

    /**
     * Handles submitted image upload form
     */
    private function handleImageUploadForm(Request $request, int $entryId): bool
    {
        /** @var EntryCrud $crud */
        $crud = $this->crud;
        /** @var Entry $entry */
        $entry = $crud->getObjectManager()->find($crud->getClass(), $entryId);

        $form = $this->getImageUploadForm($entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $data ImageUpload */
            $data = $form->getData();

            if (!$crud->isAuthor($data->getEntity()->getEntry())) {
                throw $this->createAccessDeniedException('You are not allowed to add an image to this entry.');
            }

            $this->container->get(ImageManager::class)->store($data);

            $this->container->get(FlashInterface::class)->success(__(
                'Image "%s" was uploaded successfully.',
                $data->getEntity()->getImageName()
            ));

            return true;
        }

        if ($form->isSubmitted()) {
            $this->container->get(FlashInterface::class)->error((string)$form->getErrors());

            return true;
        }


        return false;
    }

    /**
     * Create confirm form for image deletion
     *
     * @return FormInterface|Form
     */
    private function getDeleteConfirmForm(): FormInterface
    {
        /* @var $builder \Symfony\Component\Form\FormBuilder */
        $builder = $this->container->get('form.factory')->createNamedBuilder('image_delete_confirm');

        $builder
            ->add('image_id', HiddenType::class, [
                'constraints' => [new NotBlank()],
                'attr' => [
                    'value' => 0
                ]
            ])
            ->add('submit', FormActionsType::class)
        ;

        $submit = $builder->get('submit');

        $submit
            ->add('approve', SubmitType::class, [
                'label' => _('Delete'),
                'buttonClass' => 'btn-danger',
                'icon' => 'ok'
            ])
            ->add('cancel', SubmitType::class, [
                'label' => _('Cancel'),
                'buttonClass' => 'btn-default',
                'icon' => 'remove',
                'attr' => [
                    'data-dismiss' => 'modal'
                ]
            ])
        ;

        return $builder->getForm();
    }

    /**
     * Handles submitted image delete confirm form
     *
     * @param Request $request
     * @return bool
     */
    private function handleDeleteConfirmForm(Request $request): bool
    {
        $form = $this->getDeleteConfirmForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'approve') {
                $data = $form->getData();
                $imageId = $data['image_id'];

                $imageRepo = $this->getDoctrine()->getRepository(EntryImage::class);
                /** @var EntryImage $image */
                $image = $imageRepo->find($imageId);

                if ($image === null) {
                    throw $this->createNotFoundException(sprintf('An image with the ID %d was not found!', $imageId));
                }

                if (!$this->crud->isAllowedTo(CrudContract::ACTION_EDIT, $this->getUser(), $image->getEntry())) {
                    throw $this->createAccessDeniedException('You are not allowed to delete images of this entry.');
                }

                $this->container->get(ImageManager::class)->delete($image);

                // log moderative actions
                if ($image->getAuthor() !== $this->getUser()) {
                    $this->log(sprintf(
                        'Moderatives Löschen des Bildes "%s" von Beitrag "%s" von %s"',
                        $image->getImageName(),
                        $image->getEntry(),
                        $image->getAuthorDisplay()
                    ));
                }

                $this->container->get(FlashInterface::class)->success(__(
                    'Image "%s" was deleted successfully.',
                    $image->getImageName(),
                ));

                return true;
            }

            return false;
        }

        if ($form->isSubmitted()) {
            $this->container->get(FlashInterface::class)->error((string)$form->getErrors());

            return true;
        }

        return false;
    }

    /**
     * Notifies the entry author that his post is locked
     */
    private function notifyLock(Entry $entry): void
    {
        $author = $entry->getAuthor();

        if (null === $author) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }

        // don't notify the author himself, for example if he locks his own post
        if ($author === $this->getUser()) {
            return;
        }

        $dispatcher = $this->container->get(EventDispatcherInterface::class);

        $dispatcher->dispatch(new NotificationEvent(
            $author,
            'billboard',
            ['Your entry was locked: %s locked %s', (string)$this->getUser(), (string)$entry],
            'lock',
            ['billboard_show', ['id' => $entry->getId()]]
        ), NotificationEvent::NAME);
    }

    /**
     * Notifies the entry author that his post is opened
     */
    private function notifyOpen(Entry $entry): void
    {
        $author = $entry->getAuthor();

        if (null === $author) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }

        // don't notify the author himself, for example if he locks his own post
        if ($author === $this->getUser()) {
            return;
        }

        $dispatcher = $this->get('event_dispatcher');

        $dispatcher->dispatch(new NotificationEvent(
            $author,
            'billboard',
            ['Your entry was opened: %s opened %s', (string)$this->getUser(), (string)$entry],
            'pencil',
            ['billboard_show', ['id' => $entry->getId()]]
        ), NotificationEvent::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        $deps = parent::getSubscribedServices();

        $deps[] = Config::class;
        $deps[] = EventDispatcherInterface::class;
        $deps[] = FlashInterface::class;
        $deps[] = ImageManager::class;

        return $deps;
    }

    /**
     * @Route("/billboard/entry/image/{entityId}/{id}", name="billboard_fileimage_image")
     */
    public function entryImage(
        int $entityId,
        int $id,
        EntryCrud $crud,
        ImageManager $imageManager,
    ): Response {
        // Get item
        /** @var Entry $object */
        $object = $crud->getObject((string)$entityId);

        if (null === $object) {
            throw $this->createNotFoundException('Entry not found.');
        }

        // Security
        if (!$crud->isAllowedTo(CrudContract::ACTION_SHOW, $this->getUser(), $object)) {
            throw $this->createActionDeniedException('You are not allowed to view this object.');
        }

        $images = $object->getImages()->filter(static function (EntryImage $entryImage) use ($id): bool {
            return $entryImage->getId() === $id;
        });

        if ($images->isEmpty()) {
            throw $this->createNotFoundException('Entry in image not found.');
        }

        return new BinaryFileResponse($imageManager->path($images->current()));
    }
}
