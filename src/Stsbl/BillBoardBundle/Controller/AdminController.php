<?php
// src/Stsbl/BillBoardBundle/Controller/AdminController.php
namespace Stsbl\BillBoardBundle\Controller;

use IServ\CoreBundle\Controller\PageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Stsbl\BillBoardBundle\Security\Privilege;

/**
 * Controller for Bill-Board administrative page
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 * @Route("/admin/billboard")
 */
class AdminController extends PageController {
    const CONFIGDIR = '/var/lib/stsbl/billboard/cfg/';
    const FILE_RULES = 'rules.cfg';
    
    /**
     * Route for the default admin page
     * 
     * @Route("", name="admin_billboard")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $this->isAdmin();
        
        // track path
        $this->addBreadcrumb(_('Bill-Board'));
        
        return array(
            'rules_form' => $this->getRulesForm()->createView()      
        );
    }
    
    /**
     * Write new rules text to file
     * 
     * @Route("/update/rules", name="admin_billboard_update_rules")
     */
    public function updateRulesAction(Request $request)
    {
        $this->isAdmin();
        
        $form = $this->getRulesForm();
        $form->handleRequest($request);
        
        if (!$form->isValid()) {
            $this->get('iserv.flash')->error(_('Invalid rules text'));
            
            return $this->redirect($this->generateUrl('admin_billboard'));
        }
        
        $data = $form->getData();
        
        return $this->updateFile($data['rules'], self::FILE_RULES);
    }
    
    
    /**
     * Returns the current bill-board rules
     * returns the default rules, if no rules text is set
     * 
     * @return string rules
     */
    public static function getCurrentRules()
    {
        if (!is_file(self::CONFIGDIR . self::FILE_RULES) || !is_readable(self::CONFIGDIR . self::FILE_RULES)) {
            return self::getDefaultRules();
        }
        
        return file_get_contents(self::CONFIGDIR . self::FILE_RULES);
    }
    
    /**
     * Returns the translated default bill-board rules.
     * Used when no custom rules text is set
     * 
     * @return string
     */
    public static function getDefaultRules()
    {
        return _('The bill-board is only intended for small things. Please dont\'t offer things which have a worth of more than 100 euro.');
    }
    
    /**
     * Returns a Form to set the rules text
     * 
     * @return Form
     */
    private function getRulesForm()
    {
        $builder = $this->createFormBuilder();
        
        $builder
            ->setAction($this->generateUrl('admin_billboard_update_rules'))
            ->add('rules', TextareaType::class, array(
                'label' => _('Rules'),
                'data' => self::getCurrentRules(),
                'attr' => array(
                    'rows' => 10,
                    'help_text' => _('Here you can enter rules, which are shown at the form for adding an entry to the bill-board.')
                ),
                'required' => false
            ))
            ->add('submit', SubmitType::class, array('label' => _('Save'), 'buttonClass' => 'btn-success', 'icon' => 'ok'))
        ;
        
        return $builder->getForm();
    }

    /**
     * Write $content to given file inside given folder
     * and creates file and folders if necessary
     * @param  string $content  content to write
     * @param  string $filename file to write to
     * @param  string $folder   folder the file is inside of
     * @return RedirectResponse redirect to admin page
     */
    private function updateFile($content, $filename, $folder = self::CONFIGDIR)
    {
        try {
            touch($folder . $filename);
            $file = new \SplFileObject($folder . $filename, 'w');
            $file->fwrite($content);
        } catch (\RuntimeException $e) {
            $this->get('iserv.flash')->error(_('This should never happen.'));
        }

        return $this->redirect($this->generateUrl('admin_billboard'));
    }
    
    /**
     * Checks if the user has the manage privilege
     */
    private function isAdmin()
    {
        // check privilege
        $this->denyAccessUnlessGranted(Privilege::BILLBOARD_MANAGE, null, 'You need the `BILLBOARD_MANAGE` privilege to access this page.');
    }
}
