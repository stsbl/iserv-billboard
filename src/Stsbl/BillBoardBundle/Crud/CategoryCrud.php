<?php
// src/Stsbl/BillBoardBundle/Crud/CategoryListCrud.php
namespace Stsbl\BillBoardBundle\Crud;

use IServ\AdminBundle\Admin\AbstractAdmin;
use IServ\CoreBundle\Service\Logger;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use Stsbl\BillBoardBundle\Security\Privilege;

/**
 * Bill-Board category list
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class CategoryCrud extends AbstractAdmin {
    
    /**
     * Contains instance of Logger for writing logs
     * 
     * @var Logger
     */
    private $logger;
    
    protected function configure()
    {
        $this->title = _('Categories');
        $this->itemTitle = _('Category');
        $this->id = 'billboard_category';
        $this->routesNamePrefix = 'admin_';
        $this->routesPrefix = 'admin/billboard/categories/';
    }
    
    public function isAuthorized() {
        return $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }
    
    public function prepareBreadcrumbs() {
        return array(
            _('Bill-Board') => $this->router->generate('crud_billboard_index')
        );
    }
    
    /**
     * Injects the Logger into the class to write logs about category creations, updates and deletions
     * 
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }
    
    protected function getRoutePattern($action, $id, $entityBased = true)
    {
        if ('index' === $action) {
            return sprintf('%s', $this->routesPrefix);
        }
        else {
            return parent::getRoutePattern($action, 'entry', $entityBased);
        }
    }
    
    public function postPersist(CrudInterface $category) {
        $this->logger->writeForModule('Kategorie "'.$category->getTitle().'" hinzugefügt', 'Bill-Board');
    }
    
    public function postUpdate(CrudInterface $category, array $previousData = null) {
        if ($category->getTitle() !== $previousData['title']) {
            // if old and new name does not match, write a rename log
            $this->logger->writeForModule('Kategorie "'.$previousData['title'].'" umbenannt nach "'.$category->getTitle().'"', 'Bill-Board');
        } else {
            $this->logger->writeForModule('Kategorie "'.$category->getTitle().'" verändert', 'Bill-Board');
        }
    }
    
    public function postRemove(CrudInterface $category) {
        $this->logger->writeForModule('Kategorie "'.$category->getTitle().'" gelöscht', 'Bill-Board');
    }  
}
