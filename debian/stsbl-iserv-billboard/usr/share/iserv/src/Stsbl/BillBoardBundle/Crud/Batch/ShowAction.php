<?php
// src/Stsbl/BillBoardBundle/Crud/Batch/ShowAction.php
namespace Stsbl\BillBoardBundle\Crud\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CrudBundle\Crud\Batch\AbstractBatchAction;
use IServ\CrudBundle\Entity\FlashMessageBag;
use Stsbl\BillBoardBundle\Entity\Entry;

/**
 * Bill-Board show entry batch
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class ShowAction extends AbstractBatchAction {
    /**
     * {@inheritdoc}
     */
    public function execute(ArrayCollection $entries)
    {
        $bag = new FlashMessageBag();
        
        foreach ($entries as $entry) {
            $qb = $this->crud->getObjectManager()->createQueryBuilder();
            $user = $this->crud->getUser();
            try {
                $qb
                    ->update('StsblBillBoardBundle:Entry', 'e')
                    ->set('e.visible', 'true')
                    ->where('e.id = :id')
                ;
                if (!$this->crud->isModerator()) {
                    $qb
                        ->andWhere('e.author = :user')
                        ->setParameter('e.author', $user)
                    ;
                }
                
                $qb
                    ->setParameter('id', $entry->getId())
                    ->getQuery()
                    ->execute()
                ;
                
                $bag->addMessage('success', __("Entry is now visible: %s", (string) $entry));
            } catch (Exception $e) {
                $bag->addMessage('error', __("Failed to make entry visible: %s", (string) $entry));
            }
        }
        
        return $bag;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'show';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return _('Show');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getListIcon()
    {
        return 'eye-open';
    }
    
    /**
     * {@inheritdoc}
     */
    public function requiresConfirmation()
    {
        return false;
    }
}
