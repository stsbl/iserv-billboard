<?php
// src/Stsbl/BillBoardBundle/Crud/Batch/ShowAction.php
namespace Stsbl\BillBoardBundle\Crud\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CrudBundle\Crud\Batch\AbstractBatchAction;
use IServ\CrudBundle\Entity\FlashMessageBag;
use Stsbl\BillBoardBundle\Entity\Entry;

/**
 * Bill-Board hide entry batch
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 */
class HideAction extends AbstractBatchAction {
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
                    ->set('e.visible', 'false')
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
                
                $bag->addMessage('success', __("Entry is now hidden: %s", (string) $entry));
            } catch (Exception $e) {
                $bag->addMessage('error', __("Failed to hide entry: %s", (string) $entry));
            }
        }
        
        return $bag;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hide';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return _('Hide');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getListIcon()
    {
        return 'eye-close';
    }
    
    /**
     * {@inheritdoc}
     */
    public function requiresConfirmation()
    {
        return false;
    }
}

