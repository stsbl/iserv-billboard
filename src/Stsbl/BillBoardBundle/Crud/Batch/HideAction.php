<?php
// src/Stsbl/BillBoardBundle/Crud/Batch/ShowAction.php
namespace Stsbl\BillBoardBundle\Crud\Batch;

use Doctrine\Common\Collections\ArrayCollection;
use IServ\CrudBundle\Crud\Batch\AbstractBatchAction;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CrudBundle\Entity\FlashMessageBag;
use Stsbl\BillBoardBundle\Entity\Entry;
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
 * Bill-Board hide entry batch
 * 
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
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
                if ($this->isAllowedToExecute($entry, $user)) {
                    $qb
                        ->update('StsblBillBoardBundle:Entry', 'e')
                        ->set('e.visible', 'false')
                        ->where('e.id = :id')
                        ->setParameter('id', $entry->getId())
                        ->getQuery()
                        ->execute()
                    ;
                    
                    $bag->addMessage('success', __("Entry is now hidden: %s", (string) $entry));
                } else {
                    $bag->addMessage('error', __("You don't have the permission to change that entry: %s", (string) $entry));
                }
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
    
    /**
     * @param CrudInterface $entry
     * @param UserInterface $user
     */
    public function isAllowedToExecute(CrudInterface $entry, UserInterface $user) {
        if (!$this->crud->isModerator()) {
            if ($this->crud->getUser() !== $entry->getAuthor()) {
                return false;
            }
        }
        
        return true;
    }
}

