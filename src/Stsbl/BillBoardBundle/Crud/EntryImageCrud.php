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

/**
 * Bill-Board image list
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
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
