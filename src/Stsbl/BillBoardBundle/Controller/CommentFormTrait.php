<?php
// src/Stsbl/BillBoardBundle/Controller/CommentFormTrait.php
namespace Stsbl\BillBoardBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryComment;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Exception\RuntimeException;

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
 * Injects comment form creation
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
trait CommentFormTrait {   
    /**
     * Get comment form for entry with id $id.
     * 
     * @param int $id entry id
     * @return \Symfony\Component\Form\Form
     */
    public function getCommentForm($id)
    {
        $entry = $this->getEntry($id);
        $comment = new EntryComment();
        $comment->setAuthor($this->getUser());
        if (null !== $entry) {
            $comment->setEntry($entry);
        }
        $builder = $this->createFormBuilder($comment);
        
        $builder
            ->setAction($this->generateUrl('billboard_comment_add', ['entryid' => $id]))
            ->add('title', TextType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'widget_col' => 12,
                    'placeholder' => _('Title')
                    )
                )
            )
            ->add('content', TextareaType::class, array(
                'label' => false,
                'required' => true,
                'attr' => array(
                    'rows' => 4,
                    'widget_col' => 12,
                    'placeholder' => _('Comment text'),
                    )
                )
            )
            ->add('submit', SubmitType::class, array(
                'label' => _('Add'),
                'buttonClass' => 'btn-success',
                'icon' => 'ok'
                )
            )
            ->add('entry', HiddenType::class, array('data' => $entry, 'data_class' => null))
        ;
        
        $builder->get('entry')->addModelTransformer(new CallbackTransformer(
            function (Entry $entry) {
                return $entry->getId();
            },
            function ($entryid) {
                return $this->getEntry($entryid);
            }
        ));
        
        return $builder->getForm();
    }
    
    /**
     * Get confirmation form for comment with id $id.
     * 
     * @param int $id comment id
     * @return \Symfony\Component\Form\Form
     */
    public function getConfirmationForm($id)
    {
        $comment = $this->getComment($id);
        if (null === $comment) {
            throw new RuntimeException('No comment with that id found.');
        }

        $builder = $this->createFormBuilder();
        $builder
            ->setAction($this->generateUrl('billboard_comment_delete', ['id' => $id]))
            ->add('actions', FormActionsType::class)
        ;
        
        $builder->get('actions')
            ->add('approve', SubmitType::class, array(
                'label' => _('Yes'),
                'buttonClass' => 'btn-danger',
                'icon' => 'ok'
                )
            )
            ->add('cancel', SubmitType::class, array(
                'label' => _('No'),
                'buttonClass' => 'btn-default',
                'icon' => 'remove'
                )
            )
        ;
      
        return $builder->getForm();         
    }

    /**
     * Returns a entry for given id, or null if not found
     * 
     * @param $id int
     * @return Entry|null
     */
    protected function getEntry($id)
    {
        /* @var $repo EntityRepository */
        $repo = $this->getDoctrine()->getRepository('StsblBillBoardBundle:Entry');
        
        return $repo->find($id);
    }
    
    /**
     * Returns a comment for given id, or null if not found
     * 
     * @param $id int
     * @return EntryComment|null
     */
    protected function getComment($id)
    {
        /* @var $repo EntityRepository */
        $repo = $this->getDoctrine()->getRepository('StsblBillBoardBundle:EntryComment');
        
        return $repo->find($id);
    }
}
