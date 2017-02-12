<?php
// src/Stsbl/BillBoardBundle/Controller/EntryController.php
namespace Stsbl\BillBoardBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Doctrine\ORM\NoResultException;
use IServ\CrudBundle\Controller\CrudController;
use IServ\CoreBundle\Event\NotificationEvent;
use IServ\CoreBundle\Form\Type\ImageType;
use IServ\CoreBundle\Traits\LoggerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stsbl\BillBoardBundle\Controller\AdminController;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
 * The MIT License
 *
 * Copyright 2017 Felix Jacobi.
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
 */
class EntryController extends CrudController
{
    use CommentFormTrait, LoggerInitializationTrait, LoggerTrait;
    
    /**
     * Overrides default addAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @return mixed
     */
    public function addAction(Request $request) 
    {
        $ret = parent::addAction($request);
        
        if (is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }

    /**
     * Overrides default editAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @param integer $id
     * @return mixed
     */
    public function editAction(Request $request, $id) 
    {
        $ret = parent::editAction($request, $id);
        
        if (is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }

    /**
     * Overrides default showAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @param integer $id
     * @return mixed
     */
    public function showAction(Request $request, $id) 
    {
        if ($this->handleImageUploadForm($request, $id) || $this->handleDeleteConfirmForm($request)) {
            return $this->redirectToRoute('billboard_show', ['id' => $id]);
        }   
        
        $ret = parent::showAction($request, $id);
        
        if (is_array($ret)) {
            $ret['commentForm'] = $this->getCommentForm($id)->createView();
            $ret['imageUploadForm'] = $this->getImageUploadForm($id)->createView();
            $ret['imageDeleteConfirmForm'] = $this->getDeleteConfirmForm()->createView();
            $ret['commentsEnabled'] = $this->get('iserv.config')->get('BillBoardEnableComments');
            $ret['moderator'] = $this->crud->isModerator();
            
            $er = $this->getDoctrine()->getRepository('StsblBillBoardBundle:Entry');
            /* @var $entry \Stsbl\BillBoardBundle\Entity\Entry */
            $entry = $er->find($id);
            
            $ret['authorIsDeleted'] = is_null($entry->getAuthor());
            $ret['servername'] = $this->get('iserv.config')->get('Servername');
        }
        
        return $ret;
    }
    
    /**
     * Locks an opened entry
     * 
     * @param Request $request
     * @param integer $id
     * @Route("/billboard/entries/lock/{id}", name="billboard_lock")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     */
    public function lockAction(Request $request, $id)
    {
        $this->initializeLogger();
        
        /* @var $entry \Stsbl\BillBoardBundle\Entity\Entry */
        $entry = $this->getDoctrine()->getRepository('StsblBillBoardBundle:Entry')->find($id);
        $entry->setClosed(true);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();
        
        $this->notifiyLock($entry);
        $this->log(sprintf('Eintrag "%s" von %s für Schreibzugriffe gesperrt', (string)$entry, (string)$entry->getAuthor()));
        $this->get('iserv.flash')->success(sprintf(_('Entry is now locked: %s'), (string)$entry));
        
        return $this->redirect($this->generateUrl('billboard_show', ['id' => $id]));
    }
    
    /**
     * Opens an locked entry
     * 
     * @param Request $request
     * @param integer $id
     * @Route("/billboard/entries/unlock/{id}", name="billboard_unlock")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     */
    public function unlockAction(Request $request, $id)
    {
        $this->initializeLogger();
        
        /* @var $entry \Stsbl\BillBoardBundle\Entity\Entry */
        $entry = $this->getDoctrine()->getRepository('StsblBillBoardBundle:Entry')->find($id);
        $entry->setClosed(false);
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($entry);
        $em->flush();
        
        $this->notifiyOpen($entry);
        $this->log(sprintf('Eintrag "%s" von %s für Schreibzugriffe geöffnet', (string)$entry, (string)$entry->getAuthor()));
        $this->get('iserv.flash')->success(sprintf(_('Entry is now unlocked: %s'), (string)$entry));
        
        return $this->redirect($this->generateUrl('billboard_show', ['id' => $id]));
    }
    
    /**
     * Create form for image upload
     * 
     * @param integer $entryId
     * @return \Symfony\Component\Form\Form
     */
    private function getImageUploadForm($entryId)
    {
        $er = $this->getDoctrine()->getRepository('StsblBillBoardBundle:Entry');
        $entry = $er->find($entryId);
        
        $entryImage = new EntryImage();
        $entryImage->setAuthor($this->getUser());
        $entryImage->setEntry($entry);
        
        $builder = $this->createFormBuilder($entryImage);
        
        $builder
            ->add('image', ImageType::class, [
                'label' => _('Image'),
                'constraints' => [new NotBlank(['message' => _('Image should not be empty.')])]
            ])
            ->add('description', TextType::class, [
                'label' => _('Description'),
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => _('Upload'),
                'buttonClass' => 'btn-success',
                'icon' => 'pro-upload'
            ])
        ;
        
        return $builder->getForm();
    }
    
    /**
     * Handles submitted image upload form
     * 
     * @param Request $request
     * @param integer $entryId
     * @return boolean
     */
    private function handleImageUploadForm(Request $request, $entryId)
    {
        $form = $this->getImageUploadForm($entryId);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $data EntryImage */
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            
            if (!$this->crud->isAuthor($data->getEntry())) {
                throw $this->createAccessDeniedException('You are not allowed to add an image to this entry.');
            }
            
            $em->persist($data);
            $em->flush();
            
            $this->get('iserv.flash')->success(__('Image "%s" was uploaded successfully.', $data->getImage()->getFileName()));
            
            return true;
        } else if ($form->isSubmitted()) {
            $this->get('iserv.flash')->error((string)$form->getErrors());
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Create confirm form for image deletion
     * 
     * @return \Symfony\Component\Form\Form
     */
    private function getDeleteConfirmForm()
    {
        /* @var $builder \Symfony\Component\Form\FormBuilder */
        $builder = $this->get('form.factory')->createNamedBuilder('image_delete_confirm');
        
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
     * @return boolan
     */
    private function handleDeleteConfirmForm(Request $request)
    {
        $form = $this->getDeleteConfirmForm();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton()->getName() === 'approve') {
                $er = $this->getDoctrine()->getRepository('StsblBillBoardBundle:EntryImage');
                /* @var $image \Stsbl\BillBoardBundle\Entity\EntryImage */
                $image = $er->find($form->getData()['image_id']);
                
                if ($image === null) {
                    throw new NoResultException();
                }
                
                if (!$this->crud->isAllowedToEdit($image->getEntry(), $this->getUser())) {
                    throw $this->createAccessDeniedException('You are not allowed to delete images of this entry.');
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->remove($image);
                $em->flush();
                
                // log moderative actions
                if ($image->getEntry()->getAuthor() !== $this->getUser()) {
                    $this->get('iserv.logger')->writeForModule(sprintf('Moderatives Löschen des Bildes "%s" von Beitrag "%s" von %s"', (string)$image->getImage(), (string)$image->getEntry(), (string)$image->getEntry()->getAuthor()), 'Bill-Board');
                }
                
                $this->get('iserv.flash')->success(__('Image "%s" was deleted successfully.', $image->getImage()->getFileName()));
                
                return true;
            } else {
                return false;
            }
        } else if ($form->isSubmitted()) {
            $this->get('iserv.flash')->error((string)$form->getErrors());
            
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Notifies the entry author that his post is locked
     * 
     * @param Entry $entry
     * @param string type
     */
    private function notifiyLock(Entry $entry)
    {
        $author = $entry->getAuthor();
        
        if (is_null($author)) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }
        
        // don't notify the author himself, for example if he locks his own post
        if ($author === $this->getUser()) {
            return;
        }
        
        $dispatcher = $this->get('event_dispatcher');
        
        $dispatcher->dispatch(NotificationEvent::NAME, new NotificationEvent(
            $author,
            'billboard',
            ['Your entry was locked: %s locked %s', (string)$this->getUser(), (string)$entry],
            'lock',
            ['billboard_show', ['id' => $entry->getId()]]
        ));
    }
    
    /**
     * Notifies the entry author that his post is opened
     * 
     * @param Entry $entry
     * @param string type
     */
    private function notifiyOpen(Entry $entry)
    {
        $author = $entry->getAuthor();
        
        if (is_null($author)) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }
        
        // don't notify the author himself, for example if he locks his own post
        if ($author === $this->getUser()) {
            return;
        }
        
        $dispatcher = $this->get('event_dispatcher');
        
        $dispatcher->dispatch(NotificationEvent::NAME, new NotificationEvent(
            $author,
            'billboard',
            ['Your entry was opened: %s opened %s', (string)$this->getUser(), (string)$entry],
            'pencil',
            ['billboard_show', ['id' => $entry->getId()]]
        ));
    }
}
