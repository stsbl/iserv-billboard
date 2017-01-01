<?php
// src/Stsbl/BillBoardBundle/Controller/EntryController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CrudBundle\Controller\CrudController;
use Stsbl\BillBoardBundle\Controller\AdminController;
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
class EntryController extends CrudController {
    use CommentFormTrait;
    
    /**
     * Override default addAction to pass some additional variables to the template
     * 
     * @param Request $request
     * 
     * @return mixed
     */
    public function addAction(Request $request) {
        $ret = parent::addAction($request);
        
        if(is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }

    /**
     * Override default showAction to pass some additional variables to the template
     * 
     * @param Request $request
     * @param int $id
     * 
     * @return mixed
     */
    public function showAction(Request $request, $id) {
        $ret = parent::showAction($request, $id);
        
        if(is_array($ret)) {
            $ret['comment_form'] = $this->getCommentForm($id)->createView();
            $ret['comments_enabled'] = $this->get('iserv.config')->get('BillBoardEnableComments');
        }
        
        return $ret;
    }
}
