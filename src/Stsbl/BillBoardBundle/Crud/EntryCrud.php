<?php
// src/Stsbl/BillBoardBundle/Crud/EntryCrud.php
namespace Stsbl\BillBoardBundle\Crud;

use IServ\CrudBundle\Crud\AbstractCrud;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CoreBundle\Form\Type\BooleanType;
use IServ\CoreBundle\Form\Type\UserType;
use IServ\CoreBundle\Traits\LoggerTrait;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Table\ListHandler;
use IServ\CrudBundle\Table\Filter;
use Stsbl\BillBoardBundle\Crud\Batch\HideAction;
use Stsbl\BillBoardBundle\Crud\Batch\ShowAction;
use Stsbl\BillBoardBundle\Security\Privilege;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\User\UserInterface;

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
 * Bill-Board entry list
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
class EntryCrud extends AbstractCrud
{
    use LoggerTrait;
    
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
        // no prefix to remove the crud_ prefix
        $this->routesNamePrefix = '';
        $this->options['help'] = 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard';
        $this->templates['crud_add'] = 'StsblBillBoardBundle:Crud:entry_add.html.twig';
        $this->templates['crud_index'] = 'StsblBillBoardBundle:Crud:entry_index.html.twig';
        $this->templates['crud_show'] = 'StsblBillBoardBundle:Crud:entry_show.html.twig';
    }
    
    /**
     * {@inheritdoc}
     */
    protected function buildRoutes() {
        parent::buildRoutes();
        
        $this->routes[self::ACTION_ADD]['_controller'] = 'StsblBillBoardBundle:Entry:add';
        $this->routes[self::ACTION_INDEX]['_controller'] = 'StsblBillBoardBundle:Entry:index';
        $this->routes[self::ACTION_SHOW]['_controller'] = 'StsblBillBoardBundle:Entry:show';
        
        $id = $this->getId();
        $action = 'fileimage';

        // @Route("/fileimage/{entity}/{id}/{property}/{width}/{height}", name="fileimage")

        // TODO?: Solve image collection stuff.
        $this->routes['fileimage_images'] = array(
            'pattern' => sprintf('/%s%s/%s/{entity_id}/{id}/%s/{width}/{height}', $this->routesPrefix, 'entryimage', 'show', 'image'),
            'name' => sprintf('%s%s_%s', $this->routesNamePrefix, $id, $action . '_images'),
            'entity' => 'EntryImage',
            'property' => 'image',
            'width' => null,
            'height' => null,
            '_controller' => sprintf('IServCoreBundle:FileImage:%s', $action),
            '_iserv_crud' => $id,
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
            ->add('category', null, array('label' => _('Category')))
            ->add('author', UserType::class, array('label' => _('Author')))
            ->add('time', 'datetime', array('label' => _('Added')))
            ->add('updatedAt', 'datetime', array('label' => _('Last refresh')))
            ->add('images', null, array(
                'label' => _('Images'),
                'required' => false,
                'template' => 'IServCrudBundle:List:field_imagecollection.html.twig'
                )
            )
            ->add('comments', null, array(
                'label' => _('Comments'),
                'responsive' => 'desktop',
                'required' => false, 
                'template' => 'StsblBillBoardBundle:List:field_comments.html.twig'
                )
            )
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
            ->add('images', null, array('label' => _('Images'), 'required' => false, 'template' => 'IServCrudBundle:Show:field_imagecollection.html.twig'));
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
            ->add('title', null, 
                array(
                    'label' => _('Title'),
                    'attr' => array(
                        'help_text' => _('Name the item that you want to offer in one word, for example »electric guitar«.')
                    ) 
                )
            )
            ->add('category', null,
                array(
                    'label' => _('Category'),
                    'attr' => array(
                        'help_text' => _('Select the matching category for your item.')
                    )
                )
            )
            ->add('visible', BooleanType::class,
                array(
                    'label' => _('Visible'),
                    'attr' => array(
                        'help_text' => _('If you hide the entry, it is only visible by yourself and people who has the privilege to moderate the bill-board.')
                    )
                )
            )
            ->add('description', TextareaType::class,
                array(
                    'label' => _('Description'), 
                    'attr' => array(
                        'rows' => 10,
                        'help_text' => _('Give a short description of your item.')
                    )
                )
            )
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureListFilter(ListHandler $listHandler)
    {
        $qb = $this->getObjectManager()->createQueryBuilder($this->class);
        $qb->select('p')
            ->from('StsblBillBoardBundle:Entry', 'p')
            ->where('p = parent')
            ->andWhere('p.visible = true')
        ;
        
        $listHandler
            ->addListFilter((new Filter\ListPropertyFilter(_('Category'), 'category', 'StsblBillBoardBundle:Category'))->allowNone())
            ->addListFilter(new Filter\ListSearchFilter('search', ['title', 'description']))
        ;
        
        $allFilter = new Filter\ListExpressionFilter(_('All entries'), $qb->expr()->exists($qb));
        $allFilter    
            ->setName('all_entries')
            ->setListMapperUpdater(function() use ($listHandler) {
                $listHandler->disableBatchAction('show'); })
        ;
        
        $authorFilter = new Filter\ListExpressionFilter(_('Entries I created'), 'parent.author = :user and parent.visible = true');
        $authorFilter
            ->setName('created_entries')
            ->setParameters(array('user' => $this->getUser()))
            ->setListMapperUpdater(function() use ($listHandler) {
                $listHandler->disableBatchAction('show'); })
        ;
        
        $hiddenFilter = new Filter\ListExpressionFilter(_('My hidden entries'), 'parent.author = :user and parent.visible = false');
        $hiddenFilter
            ->setName('my_hidden_entries')
            ->setParameters(array('user' => $this->getUser()))
            ->setListMapperUpdater(function() use ($listHandler) {
                $listHandler->disableBatchAction('hide'); })
        ;

        $listHandler
            ->addListFilter($allFilter)
            ->addListFilter($authorFilter)
            ->addListFilter($hiddenFilter)
            ->setDefaultFilter('all_entries')
        ;
        
        if ($this->isModerator()) {
            $hiddenAllFilter = new Filter\ListExpressionFilter(_('Hidden entries of other users'), 'parent.author != :user and parent.visible = false');
            $hiddenAllFilter
                ->setName('hidden_entries_other_users')
                ->setParameters(array('user' => $this->getUser()))
                ->setListMapperUpdater(function() use ($listHandler) {
                    $listHandler->disableBatchAction('hide'); })
            ;
            
            $listHandler->addListFilter($hiddenAllFilter);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadBatchActions()
    {
        $res = parent::loadBatchActions();
        $res->add(new ShowAction($this));
        $res->add(new HideAction($this));
        
        return $res;
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
        
        $links['images'] = array($this->getRouter()->generate('crud_entryimage_index'), _('Images'), 'picture');
        
        // only add category, if user has managemant privilege and an administrator password
        if ($this->isGranted(Privilege::BILLBOARD_MANAGE)) {
            $links['categories'] = array($this->getRouter()->generate('manage_billboard_category_index'), _('Categories'), 'tags');
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
        
        if ($this->isModerator()) {
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
        || $this->isModerator()) {
            // allow users with creation nand moderation privilege to add entries
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
        
        if ($this->isModerator()) {
            // allow users with moderation and admin privilege to show hidden objects
            return true;
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function postRemove(CrudInterface $entry) {
        if ($this->isModerator()
        && $this->getUser() !== $entry->getAuthor()) {
            $this->log(sprintf('Moderatives Löschen des Eintrages "%s" von %s', $entry->getTitle(), $entry->getAuthorDisplay()));
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function postUpdate(CrudInterface $entry, array $previousData = null) {
        if ($this->isModerator()
        && $this->getUser() !== $entry->getAuthor()) {
            if ($entry->getTitle() !== $previousData['title']) {
                // write rename log, if old and new title does not match
                $this->log(sprintf('Moderatives Bearbeiten des Eintrages "%s" von %s und Umbenennen des Eintrages in "%s"', $previousData['title'], $entry->getAuthorDisplay(), $entry->getTitle()));
            } else {
                $this->log('Moderatives Bearbeiten des Eintrages "%s" von %s', $entry->getTitle(), $entry->getAuthorDisplay());
            }
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
    
    /**
     * Returns true if the current user has moderation privileges
     * 
     * @return bool
     */
    public function isModerator()
    {
        return $this->isGranted(Privilege::BILLBOARD_MODERATE)
        || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }
}
