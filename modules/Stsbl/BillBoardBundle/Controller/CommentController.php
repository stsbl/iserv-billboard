<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Controller;

use IServ\CoreBundle\Controller\AbstractPageController;
use IServ\CoreBundle\Event\NotificationEvent;
use IServ\CoreBundle\Service\Flash;
use IServ\CoreBundle\Traits\LoggerTrait;
use IServ\Library\Config\Config;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryComment;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
 * Handles adding and deleting comments
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 *
 * @Route("/billboard")
 */
final class CommentController extends AbstractPageController
{
    use CommentFormTrait;use LoggerTrait;use LoggerInitializationTrait;

    /**
     * Adds a comment
     *
     * @Route("/entry/{entry}/comment/add", name="billboard_comment_add", methods={"POST"})
     * @Security("is_granted('PRIV_BILLBOARD_CREATE') or
            is_granted('PRIV_BILLBOARD_MODERATE') or
            is_granted('PRIV_BILLBOARD_MANAGE')
       ")
     */
    public function addAction(Request $request, Entry $entry, Config $config): RedirectResponse
    {
        if (!$config->get('BillBoardEnableComments')) {
            throw $this->createAccessDeniedException('The adding of new comments was disabled by your administrator.');
        }

        $manager = $this->getDoctrine()->getManagerForClass(EntryComment::class);

        if (!$entry->isVisible() && $this->getUser() !== $entry->getAuthor() && !$this->isAllowedToDelete()) {
            throw $this->createAccessDeniedException('You don\'t have the permission to add a comment to this entry.');
        }

        if ($entry->isClosed() && !$this->isAllowedToDelete()) {
            throw $this->createAccessDeniedException(
                'The entry is currently locked for write access. You are not allowed to add a new comment.'
            );
        }

        $form = $this->getCommentForm($entry);

        $form->handleRequest($request);
        if (!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get(Flash::class)->error($error->getMessage());
            }

            return $this->redirect($this->generateUrl('billboard_show', ['id' => $entry->getId()]));
        }

        $data = $form->getData();

        $manager->persist($data);
        $manager->flush();

        // trigger notification event
        $this->notifyAuthor($entry, $data);

        $this->get(Flash::class)->success(__('Comment to entry "%s" successful added.', $entry));

        return $this->redirect($this->generateUrl('billboard_show', ['id' => $entry->getId()]));
    }

    /**
     * Deletes a comment
     *
     * @Route("/comment/delete/{id}", name="billboard_comment_delete", methods={"POST"})
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     */
    public function deleteAction(Request $request, EntryComment $comment): RedirectResponse
    {
        $form = $this->getConfirmationForm($comment);
        $manager = $this->getDoctrine()->getManager();

        $form->handleRequest($request);
        if (!$form->isValid() || !$form->isSubmitted()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get(Flash::class)->error($error->getMessage());
            }

            return $this->redirect($this->generateUrl('billboard_index'));
        }

        $button = $form->getClickedButton()->getName();
        $title = $comment->getTitle();
        $author = $comment->getAuthorDisplay();

        if ($button === 'approve') {
            $manager->remove($comment);
            $manager->flush();

            $this->log(sprintf('Moderatives Löschen des Kommentars "%s" von %s', $title, $author));
            $this->get(Flash::class)->success(__('Comment "%s" successful deleted.', $title));
        }

        return $this->redirect($this->generateUrl('billboard_show', ['id' => $comment->getEntry()->getId()]));
    }

    /**
     * Confirms the deletion of a comment
     *
     * @Route("/comment/delete/{id}/confirm", name="billboard_comment_delete_confirm")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     * @Template()
     *
     * @return array
     */
    public function confirmAction(EntryComment $comment): array
    {
        // track path
        $this->addBreadcrumb(_('Bill-Board'), $this->generateUrl('billboard_index'));
        $this->addBreadcrumb(
            $comment->getEntry(),
            $this->generateUrl('billboard_show', ['id' => $comment->getEntry()->getId()])
        );
        $this->addBreadcrumb(_('Delete comment'));

        $form = $this->getConfirmationForm($comment)->createView();

        return [
            'delete_confirm_form' => $form,
            'comment' => $comment,
            'help' => 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard'
        ];
    }

    /**
     * Checks if the user is allowed to delete comments. For this time only used for the "post comments on locked
     * entries" check above.
     *
     * @return bool
     */
    private function isAllowedToDelete(): bool
    {
        return $this->isGranted(Privilege::BILLBOARD_MODERATE)
            || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * Notifies the entry author that there is a new comment
     *
     * @param Entry $entry
     * @param EntryComment $comment
     */
    private function notifyAuthor(Entry $entry, EntryComment $comment): void
    {
        $author = $entry->getAuthor();

        if (null === $author) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }

        // don't notify the author himself, for example if he added a comment to his own entry
        if ($author === $comment->getAuthor()) {
            return;
        }

        $dispatcher = $this->get('event_dispatcher');

        $dispatcher->dispatch(new NotificationEvent(
            $author,
            'billboard',
            ['New comment on your post: %s commented on %s', (string)$this->getUser(), (string)$entry],
            'comments',
            ['billboard_show', ['id' => $entry->getId()]]
        ), NotificationEvent::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        $deps = parent::getSubscribedServices();

        $deps['event_dispatcher'] = EventDispatcherInterface::class;
        $deps[] = Flash::class;

        return $deps;
    }
}
