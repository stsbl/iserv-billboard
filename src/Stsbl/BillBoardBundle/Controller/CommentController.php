<?php
// src/Stsbl/BillBoardBundle/Controller/CommentController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CoreBundle\Controller\PageController;
use IServ\CoreBundle\Event\NotificationEvent;
use IServ\CoreBundle\Traits\LoggerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryComment;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

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
 * Handles adding and deleting comments
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
class CommentController extends PageController 
{
    use CommentFormTrait, LoggerTrait, LoggerInitializationTrait;
    
    /**
     * Adds a comment
     * 
     * @param Request $request
     * @param int $entryid
     * @return RedirectResponse
     * @Route("/billboard/entry/{entryid}/comment/add", name="billboard_comment_add")
     * @Security("is_granted('PRIV_BILLBOARD_CREATE') or is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     * @Method("POST")
     */
    public function addAction(Request $request, $entryid)
    {   
        if (!$this->get('iserv.config')->get('BillBoardEnableComments')) {
            throw $this->createAccessDeniedException('The adding of new comments was disabled by your administrator.');
        }
        
        $manager = $this->getDoctrine()->getManager();
        $entryrepo = $manager->getRepository('StsblBillBoardBundle:Entry');
        $entry = $entryrepo->find($entryid);
        if (!$entry->getVisible() && $this->getUser() !== $entry->getAuthor() && !$this->isAllowedToDelete()) {
            throw $this->createAccessDeniedException('You don\'t have the permission to add a comment to this entry.');
        }
        
        if ($entry->getClosed() && !$this->isAllowedToDelete()) {
            throw $this->createAccessDeniedException('The entry is currently locked for write access. You are not allowed to add a new comment.');
        }
        
        $form = $this->getCommentForm($entryid);
        
        $form->handleRequest($request);
        if(!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get('iserv.flash')->error($error->getMessage());
            }
            
            return $this->redirect($this->generateUrl('billboard_show', array('id' => $entryid)));
        }
        
        $data = $form->getData();

        if (null === $entry) {
            $this->get('iserv.flash')->error(_('Entry not found.'));
            
            return $this->redirect($this->generateUrl('billboard_index'));
        }
        
        $manager->persist($data);
        $manager->flush();
        
        // trigger notification event
        $this->notifyAuthor($entry, $data);
        
        $this->get('iserv.flash')->success(__('Comment to entry "%s" successful added.', (string)$entry));
        
        return $this->redirect($this->generateUrl('billboard_show', array('id' => $entryid)));
    }
    
    /**
     * Deletes a comment
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     * @Route("/billboard/comment/delete/{id}", name="billboard_comment_delete")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->getConfirmationForm($id);
        $manager = $this->getDoctrine()->getManager();
        
        $form->handleRequest($request);
        if(!$form->isValid() or !$form->isSubmitted()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get('iserv.flash')->error($error->getMessage());
            }
            
            return $this->redirect($this->generateUrl('billboard_index'));
        }
        
        $button = $form->getClickedButton()->getName();
        $comment = $this->getComment($id);
        $entryid = $comment->getEntry()->getId();
        $title = $comment->getTitle();
        $author = $comment->getAuthorDisplay();
        
        if ($button === 'approve') {
            $manager->remove($comment);
            $manager->flush();
            
            // dirty workaround: Can not run as constructor, it breaks Symfony.
            $this->initializeLogger();
            $this->log(sprintf('Moderatives Löschen des Kommentars "%s" von %s', $title, $author));
            $this->get('iserv.flash')->success(__('Comment "%s" successful deleted.', $title));
        }
        return $this->redirect($this->generateUrl('billboard_show', array('id' => $entryid)));
    }
    
    /**
     * Confirms the deletion of a comment
     * 
     * @param Request $request
     * @param int $id
     * @return array
     * @Route("/billboard/comment/delete/{id}/confirm", name="billboard_comment_delete_confirm")
     * @Security("is_granted('PRIV_BILLBOARD_MODERATE') or is_granted('PRIV_BILLBOARD_MANAGE')")
     * @Template()
     */
    public function confirmAction(Request $request, $id)
    {        
        $comment = $this->getComment($id);
        
        // track path
        $this->addBreadcrumb(_('Bill-Board'), $this->generateUrl('billboard_index'));
        $this->addBreadcrumb((string)$comment->getEntry(), $this->generateUrl('billboard_show', array('id' => $comment->getEntry()->getId())));
        $this->addBreadcrumb(_('Delete comment'));
        
        $form = $this->getConfirmationForm($id)->createView();
        return ['delete_confirm_form' => $form, 'comment' => $comment, 'help' => 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard'];
    }
    
    /**
     * Checks if the user is allowed to delete comments
     * For this time only used for the "post comments on locked entries" check above.
     * 
     * @return bool
     */
    private function isAllowedToDelete()
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
    private function notifyAuthor(Entry $entry, EntryComment $comment)
    {
        $author = $entry->getAuthor();
        
        if (is_null($author)) {
            // no notification, if there is no author (e.g. he is deleted)
            return;
        }
        
        // don't notify the author himself, for example if he added a comment to his own entry
        if ($author === $comment->getAuthor()) {
            return;
        }
        
        $dispatcher = $this->get('event_dispatcher');
        
        $dispatcher->dispatch(NotificationEvent::NAME, new NotificationEvent(
            $author,
            'billboard',
            ['New comment on your post: %s commented on %s', (string)$this->getUser(), (string)$entry],
            'comments',
            ['billboard_show', ['id' => $entry->getId()]]
        ));
    }
}
