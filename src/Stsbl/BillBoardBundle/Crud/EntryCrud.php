<?php
// src/Stsbl/BillBoardBundle/Crud/EntryCrud.php;
namespace Stsbl\BillBoardBundle\Crud;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\User\UserInterface;
use IServ\CoreBundle\Form\Type\BooleanType;
use IServ\CoreBundle\Form\Type\UserType;
use IServ\CrudBundle\Crud\AbstractCrud;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Table\ListHandler;
use IServ\CrudBundle\Table\Filter;
use IServ\CrudBundle\Entity\CrudInterface;
use Stsbl\BillBoardBundle\Security\Privilege;

/**
 * Bill-Board entry list
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class EntryCrud extends AbstractCrud
{
    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return $this->isGranted(Privilege::BILLBOARD)
        || $this->isGranted(Privilege::BILLBOARD_CREATE)
        || $this->isGranted(Privilege::BILLBOARD_MODERATE)
        || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * {@inheritdoc}
     */    
    protected function configure()
    {
        $this->title = _('Bill-Board');
        $this->itemTitle = _('Entry');
        $this->id = 'billboard';
        $this->routesPrefix = 'billboard/';
        $this->templates['crud_add'] = 'StsblBillBoardBundle:Crud:entry_add.html.twig';
        $this->templates['crud_index'] = 'StsblBillBoardBundle:Crud:entry_index.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, array('label' => _('Title')))
            ->add('category', null, array('label' => _('Category')))
            ->add('author', UserType::class, array('label' => _('Author')))
            ->add('time', 'datetime', array('label' => _('Date')))
            ->add('updatedAt', 'datetime', array('label' => _('Last refresh')))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('category', null, array('label' => _('Category')))
            ->add('author', UserType::class, array('label' => _('Author')))
            ->add('time', 'datetime', array('label' => _('Date')))
            ->add('updatedAt', 'datetime', array('label' => _('Last refresh')))
            ->add('visible', 'boolean', array('label' => _('Visible')))
            ->add('description', null, array('label' => _('Description')))
        ;
    }

    /**
     * {@inheritdoc}
     */    
    public function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->hasCategories()) {
            return;
        }
        
        $formMapper
            ->add('title', null, array('label' => _('Title')))
            ->add('category', null, array('label' => _('Category')))
            ->add('visible', BooleanType::class, array('label' => _('Visible')))
            ->add('description', TextareaType::class,
                array(
                    'label' => _('Description'), 
                    'attr' => array('rows' => 10)
                )
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(CrudInterface $entry)
    {
        $entry->setAuthor($this->getUser());
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureListFilter(ListHandler $listHandler)
    {
        $listHandler
            ->addListFilter((new Filter\ListPropertyFilter(_('Category'), 'category', 'StsblBillBoardBundle:Category'))->allowNone())
            ->addListFilter(new Filter\ListSearchFilter('search', ['title', 'description']));
        
        $filterGroup = new Filter\FilterGroup('billboard', _('All entries'), true);

        $authorFilter = new Filter\ListExpressionFilter(_('Entries I created'), 'parent.author = :user');
        $authorFilter
            ->setName('created_entries')
            ->setParameters(array('user' => $this->getUser()));
        $filterGroup->addListFilter($authorFilter);
        
        $listHandler->addListFilterGroup($filterGroup);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoutePattern($action, $id, $entityBased = true)
    {
        if ('index' === $action) {
            return sprintf('/%s', $this->routesPrefix);
        } else {
            return parent::getRoutePattern($action, 'entry', $entityBased);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexActions() {
        $links = parent::getIndexActions();
        
        if ($this->isGranted(Privilege::BILLBOARD_MANAGE)) {
            $links['categories'] = array($this->getRouter()->generate('admin_billboard_category_index'), _('Categories'), 'tags');
        }
        
        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToEdit(CrudInterface $object = null, UserInterface $user = null) {
        if ($object === null && $user === null) {
            return true;
        }
        
        if (!$this->hasCategories()) {
            return;
        }
        
        if ($object->getAuthor() === $user &&
        $this->isGranted(Privilege::BILLBOARD_CREATE)) {
            return true;
        }
        
        if ($this->isGranted(Privilege::BILLBOARD_MODERATE)
            || $this->isGranted(Privilege::BILLBOARD_MANAGE)) {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToAdd(UserInterface $user = null) {
        if ($user === null) {
            return true;
        }
        
        if ($this->isGranted(Privilege::BILLBOARD_CREATE)
        || $this->isGranted(Privilege::BILLBOARD_MODERATE)
        || $this->isGranted(Privilege::BILLBOARD_MANAGE)) {
            return true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToDelete(CrudInterface $object = null, UserInterface $user = null) {
        return $this->isAllowedToEdit($object, $user);
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function isAllowedToView(CrudInterface $object = null, UserInterface $user = null) {
        if ($object === null && $user === null) {
            return true;
        }
        
        if ($object->getVisible() === false && $user === $object->getAuthor()) {
            return true;
        }
        
        if ($object->getVisible() === true) {
            return true;
        }
    }
    
    /**
     * Returns true if there is at least one category
     *
     * @return bool
     */
    private function hasCategories()
    {
        return $this->getObjectManager()->getRepository('StsblBillBoardBundle:Category')->exists();
    }
}
