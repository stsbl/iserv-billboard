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
    /**
     * Override default addAction to pass some additional variables to the template
     * 
     * @param Request $request
     */
    public function addAction(Request $request) {
        $ret = parent::addAction($request);
        
        if(is_array($ret)) {
            $ret['rules'] = AdminController::getCurrentRules();
        }
        
        return $ret;
    }
}
