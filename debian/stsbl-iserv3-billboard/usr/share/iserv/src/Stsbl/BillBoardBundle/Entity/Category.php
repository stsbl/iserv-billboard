<?php
// src/Stsbl/BillBoardBundle/Entity/Category.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CrudBundle\Entity\CrudInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BillBoardBundle:Category
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 * @ORM\Entity(repositoryClass="Stsbl\BillBoardBundle\Entity\CategoryRepository")
 * @ORM\Table(name="billboard_category")
 */
class Category implements CrudInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * 
     * @var int
     */
    private $id;
    
    /**
     * @ORM\Column(name="title",type="text",length=255)
     * @Assert\NotBlank()
     * 
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(name="description",type="text")
     * @Assert\NotBlank()
     * 
     * @var string
     */
    private $description;
    
    /**
     * Returns a human readable string
     * 
     * @return string
     */
    public function __toString()
    {
        return (string)$this->title;
    }

    /**
     * Get id
     * 
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set title
     * 
     * @param string $title
     * 
     * @return Category
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Category
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }
}
