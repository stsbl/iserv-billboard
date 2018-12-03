<?php declare(strict_types = 1);

namespace Stsbl\BillBoardBundle\Admin;

use IServ\CoreBundle\Traits\LoggerTrait;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use Stsbl\BillBoardBundle\Entity\Category;
use Stsbl\BillBoardBundle\Security\Privilege;
use Stsbl\BillBoardBundle\Traits\LoggerInitializationTrait;

/*
 * The MIT License
 *
 * Copyright 2018 Felix Jacobi.
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
 * Bill-Board category list
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
class CategoryAdmin extends AbstractBillBoardAdmin
{
    use LoggerTrait, LoggerInitializationTrait;

    public function __construct()
    {
        parent::__construct(Category::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        parent::configure();

        $this->title = _('Categories');
        $this->itemTitle = _('Category');
        $this->id = 'billboard_category';
        $this->options['help'] = 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard';
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthorized(): bool
    {
        return $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }

    /**
     * {@inheritdoc}
     *
     * billboard/manage/category is nicer than billboard/manage/billboard_category
     */
    public function getRouteIdentifier(): string
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareBreadcrumbs(): array
    {
        if ($this->isAdmin()) {
            return [_('Bill-Board') => $this->router->generate('manage_billboard')];
        } else {
            return [_('Bill-Board') => $this->router->generate('billboard_index')];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, ['label' => _('Title')])
            ->add('description', null, ['label' => _('Description'), 'responsive' => 'desktop'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('title', null, ['label' => _('Title')])
            ->add('description', null, ['label' => _('Description')])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('title', null, ['label' => _('Title')])
            ->add('description', null, ['label' => _('Description')])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRoutePattern($action, $id, $entityBased = true): string
    {
        // nicer plural categories instead of categorys
        if (self::ACTION_INDEX === $action) {
            return sprintf('%s%s', $this->routesPrefix, 'categories');
        }

        return parent::getRoutePattern($action, $id, $entityBased);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(CrudInterface $category): void
    {
        /** @var Category $category */
        $this->log(sprintf('Kategorie "%s" hinzugefügt', $category->getTitle()));
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(CrudInterface $category, array $previousData = null): void
    {
        /** @var Category $category */
        if ($category->getTitle() !== $previousData['title']) {
            // if old and new name does not match, write a rename log
            $this->log(sprintf('Kategorie "%s" umbenannt nach "%s"', $previousData['title'], $category->getTitle()));
        } else {
            $this->log(sprintf('Kategorie "%s" verändert', $category->getTitle()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(CrudInterface $category): void
    {
        /** @var Category $category */
        $this->log(sprintf('Kategorie "%s" gelöscht', $category->getTitle()));
    }
}
