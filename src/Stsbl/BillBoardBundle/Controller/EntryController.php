<?php
// src/Stsbl/BillBoardBundle/Controller/EntryController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CrudBundle\Controller\CrudController;
use IServ\CoreBundle\Traits\LoggerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Stsbl\BillBoardBundle\Controller\AdminController;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
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
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
class EntryController extends CrudController
{
    use CommentFormTrait, LoggerInitializationTrait, LoggerTrait;
    
    /**
     * Override default addAction to pass some additional variables to the template
     * 
     * @param Request $request
     * 
     * @return mixed
     */
    public function addAction(Request $request) 
    {
        $ret = parent::addAction($request);
        
        if(is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }

    /**
     * Override default editAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @param integer $id
     * 
     * @return mixed
     */
    public function editAction(Request $request, $id) 
    {
        $ret = parent::editAction($request, $id);
        
        if(is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }

    /**
     * Override default showAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @param integer $id
     * 
     * @return mixed
     */
    public function showAction(Request $request, $id) 
    {
        $ret = parent::showAction($request, $id);
        
        if(is_array($ret)) {
            $ret['comment_form'] = $this->getCommentForm($id)->createView();
            $ret['comments_enabled'] = $this->get('iserv.config')->get('BillBoardEnableComments');
            $ret['moderator'] = $this->crud->isModerator();
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
        
        $this->log(sprintf('Eintrag "%s" von %s für Schreibzugriffe geöffnet', (string)$entry, (string)$entry->getAuthor()));
        $this->get('iserv.flash')->success(sprintf(_('Entry is now unlocked: %s'), (string)$entry));
        
        return $this->redirect($this->generateUrl('billboard_show', ['id' => $id]));
    }
}
