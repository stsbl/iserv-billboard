<?php
// src/Stsbl/BillBoardBundle/Controller/CommentController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CoreBundle\Controller\PageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Handles adding comments
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class CommentController extends PageController {
    use CommentFormTrait;
    
    /**
     * Adds a comment
     * 
     * @param Request $request
     * @param int $entryid
     * @Route("/billboard/entry/{entryid}/comment/add", name="billboard_comment_add")
     * @Method("POST")
     */
    public function addAction(Request $request, $entryid)
    {
        // Check privilege
        if (!$this->isAllowedToAdd()) {
            throw new AccessDeniedHttpException('You don\'t have the permission to add a comment.');
        }
        
        $manager = $this->getDoctrine()->getManager();
        $entryrepo = $manager->getRepository('StsblBillBoardBundle:Entry');
        $entry = $entryrepo->find($entryid);
        if (!$entry->getVisible() && $this->getUser() !== $entry->getAuthor() && !$this->isAllowedToDelete()) {
            throw new AccessDeniedHttpException('You don\'t have the permission to add a comment to this entry.');
        }
        
        $form = $this->getCommentForm($entryid);
        
        $form->handleRequest($request);
        if(!$form->isValid()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get('iserv.flash')->error($error->getMessage());
            }
            
            return $this->redirect($this->generateUrl('crud_billboard_show', array('id' => $entryid)));
        }
        
        $data = $form->getData();

        if (null === $entry) {
            $this->get('iserv.flash')->error(_('Entry not found.'));
            
            return $this->redirect($this->generateUrl('crud_billboard_index'));
        }
        
        $manager->persist($data);
        $manager->flush();
        $this->get('iserv.flash')->success(__('Comment to entry "%s" successful added.', (string)$entry));
        
        return $this->redirect($this->generateUrl('crud_billboard_show', array('id' => $entryid)));
    }
    
    /**
     * Deletes a comment
     * 
     * @param Request $request
     * @param int $commentid
     * @Route("/billboard/comment/delete/{commentid}", name="billboard_comment_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $commentid)
    {
        // Check privilege
        if (!$this->isAllowedToDelete()) {
            throw new AccessDeniedHttpException('You don\'t have the permission to delete comments.');
        }
        $form = $this->getConfirmationForm($commentid);
        $manager = $this->getDoctrine()->getManager();
        
        $form->handleRequest($request);
        if(!$form->isValid() or !$form->isSubmitted()) {
            foreach ($form->getErrors(true, true) as $error) {
                $this->get('iserv.flash')->error($error->getMessage());
            }
            
            return $this->redirect($this->generateUrl('crud_billboard_index'));
        }
        
        $button = $form->getClickedButton()->getName();
        $comment = $this->getComment($commentid);
        $entryid = $comment->getEntry()->getId();
        $title = $comment->getTitle();
        $author = $comment->getAuthorDisplay();
        
        if ($button === 'approve') {
            $manager->remove($comment);
            $manager->flush();
            
            $this->get('stsbl.billboard.logging_service')->writeLog(sprintf('Moderatives Löschen des Kommentars "%s" von %s', $title, $author));
            $this->get('iserv.flash')->success(__('Comment "%s" successful deleted.', $title));
        }
        return $this->redirect($this->generateUrl('crud_billboard_show', array('id' => $entryid)));
    }
    
    /**
     * Confirms the deletion of a comment
     * 
     * @param Request $request
     * @param int $commentid
     * @Route("/billboard/comment/delete/{commentid}/confirm", name="billboard_comment_delete_confirm")
     */
    public function confirmAction(Request $request, $commentid)
    {
        // Check privilege
        if (!$this->isAllowedToDelete()) {
            throw new AccessDeniedHttpException('You don\'t have the permission to delete comments.');
        }
        
        $comment = $this->getComment($commentid);
        
        // track path
        $this->addBreadcrumb(_('Bill-Board'), $this->generateUrl('crud_billboard_index'));
        $this->addBreadcrumb((string)$comment->getEntry(), $this->generateUrl('crud_billboard_show', array('id' => $comment->getEntry()->getId())));
        $this->addBreadcrumb(_('Delete comment'));
        
        $form = $this->getConfirmationForm($commentid)->createView();
        return $this->render('StsblBillBoardBundle:Comment:delete_confirm.html.twig', array('delete_confirm_form' => $form, 'comment' => $comment));
    }
    
    /**
     * Checks if the user is allowed to delete comments
     * 
     * @return bool
     */
    private function isAllowedToDelete()
    {
        return $this->isGranted('PRIV_BILLBOARD_MODERATE')
            || $this->isGranted('PRIV_BILLBOARD_MANAGE');
    }
    
    /**
     * Checks if the user is allowed to add comments
     * 
     * @return bool
     */
    private function isAllowedToAdd()
    {
        return $this->isGranted('PRIV_BILLBOARD_CREATE')
            || $this->isGranted('PRIV_BILLBOARD_MODERATE')
            || $this->isGranted('PRIV_BILLBOARD_MANAGE');
    }
}
