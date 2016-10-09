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

/**
 * Injects comment form creation
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
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
