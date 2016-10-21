<?php
// src/Stsbl/BillBoardBundle/Crud/CategoryListCrud.php
namespace Stsbl\BillBoardBundle\Admin;

use IServ\AdminBundle\Admin\AbstractAdmin;
use IServ\CoreBundle\Traits\LoggerTrait;
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
    use LoggerTrait;
    
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
        $this->routesPrefix = 'admin/billboard/categories/';
        // $this->options['help'] = 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard';
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
     * {@inheritdoc}
     */
    public function __construct($class, $title = null, $itemTitle = null) {
        // set module context for logging
        $this->logModule = 'Bill-Board';
        
        return parent::__construct($class, $title, $itemTitle);
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
        // Overwrite broken route generation of Crud (WHY? =()
        if ('index' === $action) {
            return sprintf('%s', $this->routesPrefix);
        } else if ('add' === $action) {
            return sprintf('%s%s', $this->routesPrefix, $action);
        } else if ('batch' === $action) {
            return sprintf('%s%s', $this->routesPrefix, $action);
        } else if ('batch/confirm' === $action) {
            return sprintf('%s%s/%s', $this->routesPrefix, 'batch', 'confirm');
        } else if ('show' === $action) {
            return sprintf('%s%s/%s', $this->routesPrefix, $action, '{id}');
        } else if ('edit' === $action) {
            return sprintf('%s%s/%s', $this->routesPrefix, $action, '{id}');
        } else if ('delete' === $action) {
           return sprintf('%s%s/%s', $this->routesPrefix, $action, '{id}');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(CrudInterface $category)
    {
        $this->log('Kategorie "'.$category->getTitle().'" hinzugefügt');
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(CrudInterface $category, array $previousData = null)
    {
        if ($category->getTitle() !== $previousData['title']) {
            // if old and new name does not match, write a rename log
            $this->log('Kategorie "'.$previousData['title'].'" umbenannt nach "'.$category->getTitle().'"');
        } else {
            $this->log('Kategorie "'.$category->getTitle().'" verändert');
        }
    }

    /**
     * {@inheritdoc}
     */    
    public function postRemove(CrudInterface $category)
    {
        $this->log('Kategorie "'.$category->getTitle().'" gelöscht');
    }  
}
