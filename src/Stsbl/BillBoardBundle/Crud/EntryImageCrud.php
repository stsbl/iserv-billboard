<?php
// src/Stsbl/BillBoardBundle/Crud/ImageCrud.php
namespace Stsbl\BillBoardBundle\Crud;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CoreBundle\Form\Type\ImageType;
use IServ\CrudBundle\Crud\AbstractCrud;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Mapper\FormMapper;
use IServ\CrudBundle\Mapper\ListMapper;
use IServ\CrudBundle\Mapper\ShowMapper;
use Stsbl\BillBoardBundle\Security\Privilege;

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
 * Bill-Board image list
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
class EntryImageCrud extends AbstractCrud {
    /**
     * {@inheritdoc}
     */
    public function isAuthorized()
    {
        return $this->isGranted(Privilege::BILLBOARD_CREATE)
        || $this->isGranted(Privilege::BILLBOARD_MODERATE)
        || $this->isGranted(Privilege::BILLBOARD_MANAGE);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
       $this->title = _('Images');
       $this->itemTitle = _('Image');
       // $this->id = 'billboard_images';
       $this->routesPrefix = 'billboard/images/';
       $this->options['help'] = 'https://it.stsbl.de/documentation/mods/stsbl-iserv-billboard';
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
            ->addIdentifier('image', ImageType::class, array('label' => _('Image')))
            ->add('description', null, array('label' => _('Description')))
            ->add('entry', null, array('label' => _('Entry')))
            ->add('time', 'datetime', array('label' => _('Initial Upload')))
            ->add('updatedAt', 'datetime', array('label' => _('Last refresh'), 'responsive' => 'desktop'))
        ;
                
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureShowFields(ShowMapper $showMapper) {
        $showMapper
            ->add('image', ImageType::class, array('label' => _('Image')))
            ->add('description', null, array('label' => _('Description')))
            ->add('entry', null, array('label' => _('Entry')))
            ->add('time', 'datetime', array('label' => _('Initial Upload')))
            ->add('updatedAt', 'datetime', array('label' => _('Last refresh')))

        ;
                
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper) {
        $qb = $this->getObjectManager()->createQueryBuilder($this->class);
        $qb->select('p')
            ->from('StsblBillBoardBundle:Entry', 'p')
            ->where('p.author = :user')
            ->setParameter('user', $this->getUser())
        ;
        
        $entryList = new ArrayCollection($qb->getQuery()->getResult());
        
        $formMapper
            ->add('image', ImageType::class, array('label' => _('Image'), 'required' => true))
            ->add('description', null, array('label' => _('Description')))
            ->add('entry', null, array('label' => _('Entry'), 'choices' => $entryList))
        ;
                
    }
    
    /**
     * {@inheritdoc}
     */
    public function prePersist(CrudInterface $image)
    {
        $image->setAuthor($this->getUser());
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
    public function prepareBreadcrumbs()
    {
        return array(
            _('Bill-Board') => $this->router->generate('crud_billboard_index')
        );
    }
}
