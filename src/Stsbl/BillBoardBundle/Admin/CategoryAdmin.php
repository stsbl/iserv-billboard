<?php
// src/Stsbl/BillBoardBundle/Crud/CategoryListCrud.php
namespace Stsbl\BillBoardBundle\Admin;

use IServ\AdminBundle\Admin\AbstractAdmin;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Service\LoggingService;

/**
 * Bill-Board category list
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class CategoryAdmin extends AbstractAdmin {
    /**
     * Contains instance of LoggingService for writing logs
     * 
     * @var LoggingService
     */
    private $loggingService;
    
    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * {@inheritdoc}
     */    
    protected function configure()
    {
        $this->title = _('Categories');
        $this->itemTitle = _('Category');
        $this->id = 'billboard_category';
        $this->routesNamePrefix = 'admin_';
        $this->routesPrefix = 'admin/billboard/categories/';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareBreadcrumbs()
    {
        return array(
            _('Bill-Board') => $this->router->generate('crud_billboard_index')
        );
    }
    
    /**
     * Injects the Logger into the class to write logs about category creations, updates and deletions
     * 
     * @param LoggingService $loggingService
     */
    public function setLoggingService(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description'), 'responsive' => 'desktop'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoutePattern($action, $id, $entityBased = true)
    {
        if ('index' === $action) {
            return sprintf('%s', $this->routesPrefix);
        }
        else {
            return sprintf('/%s/%s%s', $this->routesPrefix, $action, $entityBased ? '/{id}' : '');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(CrudInterface $category)
    {
        $this->loggingService->writeLog('Kategorie "'.$category->getTitle().'" hinzugefügt');
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(CrudInterface $category, array $previousData = null)
    {
        if ($category->getTitle() !== $previousData['title']) {
            // if old and new name does not match, write a rename log
            $this->loggingService->writeLog('Kategorie "'.$previousData['title'].'" umbenannt nach "'.$category->getTitle().'"');
        } else {
            $this->loggingService->writeLog('Kategorie "'.$category->getTitle().'" verändert');
        }
    }

    /**
     * {@inheritdoc}
     */    
    public function postRemove(CrudInterface $category)
    {
        $this->loggingService->writeLog('Kategorie "'.$category->getTitle().'" gelöscht');
    }  
}
