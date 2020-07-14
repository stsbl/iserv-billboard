<?php
declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Crud;

use IServ\CoreBundle\Form\Type\BooleanType;
use IServ\CoreBundle\Form\Type\ImageType;
use IServ\CoreBundle\Form\Type\PurifiedTextareaType;
use IServ\CoreBundle\Traits\LoggerTrait;
use IServ\CoreBundle\Util\Collection\OrderedCollection;
use IServ\CrudBundle\Crud\AbstractCrud;
use IServ\CrudBundle\Crud\Action\Link;
use IServ\CrudBundle\Crud\Batch\BatchActionCollection;
use IServ\CrudBundle\Crud\FileImageRoutingTrait;
use IServ\CrudBundle\Doctrine\ORM\ORMObjectManager;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use IServ\CrudBundle\Table\Filter;
use IServ\CrudBundle\Table\ListHandler;
use Stsbl\BillBoardBundle\Controller\EntryController;
use Stsbl\BillBoardBundle\Crud\Batch\HideAction;
use Stsbl\BillBoardBundle\Crud\Batch\ShowAction;
use Stsbl\BillBoardBundle\Entity\Category;
use Stsbl\BillBoardBundle\Entity\CategoryRepository;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;
use Symfony\Component\Security\Core\User\UserInterface;

/*
 * The MIT License
 *
 * Copyright 2020 Felix Jacobi.
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
    use FileImageRoutingTrait, LoggerTrait, LoggerInitializationTrait;

    public function __construct()
    {
        parent::__construct(Entry::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->title = _('Bill-Board');
        $this->itemTitle = _('Entry');
        $this->id = 'billboard';
        $this->routesPrefix = 'billboard/';
        $this->routesNamePrefix = '';
        $this->options['help'] = 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard';
        $this->templates['crud_add'] = 'StsblBillBoardBundle:Crud:entry_add.html.twig';
        $this->templates['crud_edit'] = 'StsblBillBoardBundle:Crud:entry_edit.html.twig';
        $this->templates['crud_index'] = 'StsblBillBoardBundle:Crud:entry_index.html.twig';
        $this->templates['crud_show'] = 'StsblBillBoardBundle:Crud:entry_show.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized(): bool
    {
        return $this->isGranted(Privilege::BILLBOARD)
            || $this->isGranted(Privilege::BILLBOARD_CREATE)
            || $this->isGranted(Privilege::BILLBOARD_MODERATE)
            || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * {@inheritdoc}
     *
     * billboard/entry is nicer than billboard/billboard
     */
    public function getRouteIdentifier(): string
    {
        return 'entry';
    }

    /**
     * {@inheritdoc}
     */
    protected function buildRoutes(): void
    {
        parent::buildRoutes();

        $routeId = $this->getRouteIdentifier();
        $fileImageProperty = 'image';

        $this->buildImageRoute($fileImageProperty);

        $this->routes['fileimage_image']['_controller'] = EntryController::class . '::entryImage';
        $this->routes['fileimage_image']['pattern'] = sprintf(
            '/%s%s/image/{entityId}/{id}/%s/{width}/{height}',
            $this->getRoutesPrefix(),
            $routeId,
            $fileImageProperty
        );

        $this->routes[self::ACTION_ADD]['_controller'] = EntryController::class . '::addAction';
        $this->routes[self::ACTION_EDIT]['_controller'] = EntryController::class . '::editAction';
        $this->routes[self::ACTION_INDEX]['_controller'] = EntryController::class . '::indexAction';
        $this->routes[self::ACTION_SHOW]['_controller'] = EntryController::class . '::showAction';
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => _('Title'),
                'responsive' => 'all',
            ])
            ->add('category', null, [
                'label' => _('Category'),
                'responsive' => 'all',
            ])
            ->add('author', null, [
                'label' => _('Author'),
                'responsive' => 'min-tablet',
            ])
            ->add('time', 'datetime', [
                'label' => _('Added'),
                'responsive' => 'min-tablet',
            ])
            ->add('updatedAt', 'datetime', [
                'label' => _('Last refresh'),
                'responsive' => 'min-tablet',
            ])
            ->add('images', ImageType::class, [
                'label' => _('Images'),
                'required' => false,
                'template' => '@StsblBillBoard/List/field_imagecollection.html.twig',
                'image_route' => 'billboard_fileimage_image',
            ])
            ->add('comments', null, [
                'label' => _('Comments'),
                'responsive' => 'desktop',
                'required' => false,
                'template' => '@StsblBillBoard/List/field_comments.html.twig',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('title', null, [
                'label' => _('Title'),
            ])
            ->add('category', null, [
                'label' => _('Category')
            ])
            ->add('author', null, [
                'label' => _('Author'),
            ])
            ->add('time', 'datetime', [
                'label' => _('Date'),
            ])
            ->add('updatedAt', 'datetime', [
                'label' => _('Last refresh'),
            ])
            ->add('visible', 'boolean', [
                'label' => _('Visible'),
            ])
            ->add('description', null, [
                'label' => _('Description'),
                'template' => '@StsblBillBoard/List/field_formatted_content.html.twig',
            ])
            ->add('images', null, [
                'label' => _('Images'),
                'hideIfEmpty' => true,
                'template' => '@IServCrud/Show/field_imagecollection.html.twig',
            ]);
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper): void
    {
        if (!$this->hasCategories()) {
            return;
        }
        
        $formMapper
            ->add('title', null, [
                'label' => _('Title'),
                'attr' => [
                    'help_text' => _('Name the matter that you want to offer in one sentence, for example '.
                        '»I am oferring my electric guitar for selling«.'),
                ]
            ])
            ->add('category', null, [
                'label' => _('Category'),
                'attr' => [
                    'help_text' => _('Select the matching category for your matter.')
                ]
            ])
            ->add('visible', BooleanType::class, [
                'label' => _('Visible'),
                'attr' => [
                    'help_text' => _('If you hide the entry, it is only visible by yourself and people who has the '.
                        'privilege to moderate the bill-board.')
                ]
            ])
            ->add('description', PurifiedTextareaType::class, [
                'label' => _('Description'),
                'attr' => [
                    'rows' => 30,
                    'help_text' => _('Please give a short description of your matter.')
                ]
            ])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureListFilter(ListHandler $listHandler): void
    {
        /** @var ORMObjectManager $em */
        $em = $this->getObjectManager();
        $qb = $em->createQueryBuilder($this->class);

        $qb->select('p')
            ->from('StsblBillBoardBundle:Entry', 'p')
            ->where($qb->expr()->eq('p', 'parent'))
            ->andWhere('p.visible = true')
        ;
        
        $listHandler
            ->addListFilter(
                (new Filter\ListPropertyFilter(_('Category'), 'category', 'StsblBillBoardBundle:Category'))
                    ->allowNone()
            )
            ->addListFilter(new Filter\ListSearchFilter('search', ['title', 'description']))
        ;
        
        $allFilter = new Filter\ListExpressionFilter(_('All entries'), $qb->expr()->exists($qb));
        $allFilter
            ->setName('all_entries')
            ->setListMapperUpdater(function () use ($listHandler) {
                $listHandler->disableBatchAction('show');
            })
        ;
        
        $authorFilter = new Filter\ListExpressionFilter(
            _('Entries I created'),
            'parent.author = :user and parent.visible = true'
        );

        $authorFilter
            ->setName('created_entries')
            ->setParameters(['user' => $this->getUser()])
            ->setListMapperUpdater(function () use ($listHandler) {
                $listHandler->disableBatchAction('show');
            })
        ;
        
        $hiddenFilter = new Filter\ListExpressionFilter(_('My hidden entries'), $qb->expr()->andX(
            $qb->expr()->eq('parent.author', ':user'),
            $qb->expr()->eq('parent.visible', 'false')
        ));
        $hiddenFilter
            ->setName('my_hidden_entries')
            ->setParameters(['user' => $this->getUser()])
            ->setListMapperUpdater(function () use ($listHandler) {
                $listHandler->disableBatchAction('hide');
            })
        ;

        $listHandler
            ->addListFilter($allFilter)
            ->addListFilter($authorFilter)
            ->addListFilter($hiddenFilter)
            ->setDefaultFilter('all_entries')
        ;
        
        if ($this->isModerator()) {
            $hiddenAllFilter = new Filter\ListExpressionFilter(_('Hidden entries of other users'), $qb->expr()->andX(
                $qb->expr()->neq('parent.author', ':user'),
                $qb->expr()->eq('parent.visible', 'false')
            ));
            $hiddenAllFilter
                ->setName('hidden_entries_other_users')
                ->setParameters(['user' => $this->getUser()])
                ->setListMapperUpdater(function () use ($listHandler) {
                    $listHandler->disableBatchAction('hide');
                })
            ;
            
            $listHandler->addListFilter($hiddenAllFilter);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function loadBatchActions(): BatchActionCollection
    {
        $res = parent::loadBatchActions();

        $res->add(new ShowAction($this));
        $res->add(new HideAction($this));
        
        return $res;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getShowActions(CrudInterface $item): OrderedCollection
    {
        /* @var $item \Stsbl\BillBoardBundle\Entity\Entry */
        $links = parent::getShowActions($item);
        
        if ($this->isModerator()) {
            if ($item->isClosed()) {
                $links->set('unlock', Link::create($this->getRouter()->generate(
                    'billboard_unlock',
                    ['id' => $item->getId()]
                ), _('Open'), 'pro-unlock'));
            } else {
                $links->set('lock', Link::create($this->getRouter()->generate(
                    'billboard_lock',
                    ['id' => $item->getId()]
                ), _p('billboard', 'Lock'), 'lock'));
            }
        }
        
        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(CrudInterface $entry): void
    {
        /* @var Entry $entry */
        $entry->setAuthor($this->getUser());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoutePattern($action, $id, $entityBased = true): string
    {
        // nicer plural entries instead of entrys
        if ('index' === $action) {
            return sprintf('%s%s', $this->routesPrefix, 'entries');
        }

        return parent::getRoutePattern($action, $id, $entityBased);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIndexActions(): OrderedCollection
    {
        $links = parent::getIndexActions();
        
        // only add category, if user has management privilege
        if ($this->isGranted(Privilege::BILLBOARD_MANAGE)) {
            $links->set('categories', Link::create(
                $this->getRouter()->generate('manage_billboard_category_index'),
                _('Categories'),
                'tags'
            ));
        }
        
        return $links;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToEdit(CrudInterface $object = null, UserInterface $user = null): bool
    {
        /** @var $object Entry */
        if ($object === null && $user === null) {
            return true;
        }
        
        if (!$this->hasCategories()) {
            return false;
        }
        
        // only allow moderators to edit locked entries
        if ($object->isClosed() && !$this->isModerator()) {
            return false;
        }
        
        if ($object->getAuthor() === $user &&
        $this->isGranted(Privilege::BILLBOARD_CREATE)) {
            return true;
        }
        
        if ($this->isModerator()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToAdd(UserInterface $user = null): bool
    {
        if ($user === null) {
            return true;
        }
        
        if ($this->isGranted(Privilege::BILLBOARD_CREATE)
        || $this->isModerator()) {
            // allow users with creation and moderation privilege to add entries
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowedToDelete(CrudInterface $object = null, UserInterface $user = null): bool
    {
        return $this->isAllowedToEdit($object, $user);
    }
    
    /**
     * 
     * {@inheritdoc}
     */
    public function isAllowedToView(CrudInterface $object = null, UserInterface $user = null): bool
    {
        /* @var Entry $object */
        if ($object === null && $user === null) {
            return true;
        }
        
        if ($object->isVisible() === false && $user === $object->getAuthor()) {
            return true;
        }
        
        if ($object->isVisible() === true) {
            return true;
        }
        
        if ($this->isModerator()) {
            // allow users with moderation and admin privilege to show hidden objects
            return true;
        }

        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function postRemove(CrudInterface $entry): void
    {
        /* @var Entry $entry */
        if ($this->isModerator() && $this->getUser() !== $entry->getAuthor()) {
            $this->log(sprintf(
                'Moderatives Löschen des Eintrages "%s" von %s',
                $entry->getTitle(),
                $entry->getAuthorDisplay()
            ));
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function postUpdate(CrudInterface $entry, array $previousData = null): void
    {
        /* @var Entry $entry */
        if ($this->isModerator() && $this->getUser() !== $entry->getAuthor()) {
            if ($entry->getTitle() !== $previousData['title']) {
                // write rename log, if old and new title does not match
                $this->log(sprintf(
                    'Moderatives Bearbeiten des Eintrages "%s" von %s und Umbenennen des Eintrages in "%s"',
                    $previousData['title'],
                    $entry->getAuthorDisplay(),
                    $entry->getTitle()
                ));
            } else {
                $this->log(sprintf(
                    'Moderatives Bearbeiten des Eintrages "%s" von %s',
                    $entry->getTitle(),
                    $entry->getAuthorDisplay()
                ));
            }
        }
    }


    /**
     * Returns true if there is at least one category
     */
    private function hasCategories(): bool
    {
        /** @var ORMObjectManager $em */
        $em = $this->getObjectManager();
        /** @var CategoryRepository $repo */
        $repo = $em->getRepository(Category::class);

        return $repo->exists();
    }
    
    /**
     * Returns true if the current user has moderation privileges
     */
    public function isModerator(): bool
    {
        return $this->isGranted(Privilege::BILLBOARD_MODERATE)
        || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * Returns true if current user is author of the given post
     */
    public function isAuthor(CrudInterface $object): bool
    {
        /* @var $object Entry */
        return $object->getAuthor() === $this->getUser();
    }
}
