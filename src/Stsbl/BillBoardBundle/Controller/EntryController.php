<?php
// src/Stsbl/BillBoardBundle/Controller/EntryController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CrudBundle\Controller\CrudController;
use Stsbl\BillBoardBundle\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
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
        }
        
        return $ret;
    }
}
