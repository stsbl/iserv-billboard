<?php
declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Controller;

use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use IServ\CoreBundle\Entity\User;
use Stsbl\BillBoardBundle\Entity\Entry;
use Stsbl\BillBoardBundle\Entity\EntryComment;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
 * Injects comment form creation
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://mit.otg/licenses/MIT>
 */
trait CommentFormTrait
{
    /**
     * @return RegistryInterface|ManagerRegistry
     */
    abstract protected function getDoctrine();

    /**
     * @return User|UserInterface
     */
    abstract protected function getUser();

    /**
     * @param mixed $data
     * @param array $options
     * @return FormBuilderInterface
     */
    abstract protected function createFormBuilder($data = null, array $options = []);

    /**
     * Get comment form for entry with id $id.
     *
     * @return FormInterface
     */
    public function getCommentForm(Entry $entry): FormInterface
    {
        $comment = EntryComment::createForEntryAndUser($entry, $this->getUser());

        $builder = $this->createFormBuilder($comment);
        
        $builder
            ->setAction($this->generateUrl('billboard_comment_add', ['entry' => $entry->getId()]))
            ->add('title', TextType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'widget_col' => 12,
                    'placeholder' => _('Title')
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'rows' => 4,
                    'widget_col' => 12,
                    'placeholder' => _('Comment text'),
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => _('Add'),
                'buttonClass' => 'btn-success',
                'icon' => 'ok'
            ])
            ->add('entry', HiddenType::class, ['data' => $entry, 'data_class' => null])
        ;
        
        $builder->get('entry')->addModelTransformer(new CallbackTransformer(
            function (Entry $entry) {
                return $entry->getId();
            },
            function (int $entryId = null): ?Entry {
                if (null === $entryId) {
                    return null;
                }

                return $this->getEntry($entryId);
            }
        ));
        
        return $builder->getForm();
    }

    /**
     * Get confirmation form for comment with id $id.
     *
     * @return FormInterface|Form
     */
    protected function getConfirmationForm(EntryComment $comment): FormInterface
    {
        $builder = $this->createFormBuilder();
        $builder
            ->setAction($this->generateUrl('billboard_comment_delete', ['id' => $comment->getId()]))
            ->add('actions', FormActionsType::class)
        ;
        
        $builder->get('actions')
            ->add('approve', SubmitType::class, [
                'label' => _('Yes'),
                'buttonClass' => 'btn-danger',
                'icon' => 'ok'
            ])
            ->add('cancel', SubmitType::class, [
                'label' => _('No'),
                'buttonClass' => 'btn-default',
                'icon' => 'remove'
            ])
        ;
      
        return $builder->getForm();
    }

    /**
     * Returns a entry for given id, or null if not found
     */
    protected function getEntry(int $id): ?Entry
    {
        $repo = $this->getDoctrine()->getRepository(Entry::class);
        
        return $repo->find($id);
    }
    
    /**
     * Returns a comment for given id, or null if not found
     */
    protected function getComment(int $id): ?EntryComment
    {
        $repo = $this->getDoctrine()->getRepository(EntryComment::class);
        
        return $repo->find($id);
    }
}
