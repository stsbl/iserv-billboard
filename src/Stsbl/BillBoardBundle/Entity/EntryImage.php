<?php
// src/Stsbl/BillBoardBundle/Entity/Image.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\CoreBundle\Entity\FileImage;
use IServ\CoreBundle\Entity\User;
use Stsbl\BillBoardBundle\Entity\Entry;

/**
 * BillBoardBundle:EntryImage
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license GNU General Public License <http://gnu.org/licenses/gpl-3.0>
 * @ORM\Entity
 * @ORM\Table(name="billboard_images")
 * @ORM\HasLifecycleCallbacks
 */
class EntryImage implements CrudInterface
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
     * @ORM\Column(type="file_image",  nullable=true)
     * 
     * @var FileImage
     */
    private $image;
    
    /**
     * @ORM\Column(name="description",type="text",nullable=true)
     * 
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="author", referencedColumnName="act")
     *
     * @var User
     */
    private $author;
    
    /**
     * @ORM\Column(name="time",type="datetime",nullable=false)
     * 
     * @var \DateTime
     */
    private $time;
    
    /**
     * @ORM\Column(name="updated_at",type="datetime",nullable=false)
     * 
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="images")
     * @ORM\JoinColumn(name="entry", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @var Entry
     */
    private $entry;

    /**
     * Lifecycle callback to set the creation date
     *
     * @ORM\PrePersist
     */
    public function onCreate()
    {
        $this->setTime(new \DateTime("now"));
        $this->updateLastUpdatedTime();
    }
    
    /**
     * Lifecycle callback to set the update date
     * 
     * @ORM\PreUpdate
     */
    public function onUpdate()
    {
        $this->updateLastUpdatedTime();
    }

    /**
     * Updates last updated time to 'now'
     */
    public function updateLastUpdatedTime()
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }
    
    /**
     * Returns a human readable string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getImage()->getFileName() ? (string)$this->getImage()->getFileName() : '';
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
     * Get image
     * 
     * @return FileImage
     */
    public function getImage()
    {
        return $this->image;
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
     * Get author
     * 
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }
    
    /**
     * Get time
     * 
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }
    
    /**
     * Get updatedAt
     * 
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    /**
     * Get entry
     * 
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }
    
    /**
     * Set image
     * 
     * @param FileImage $image
     * 
     * @return Image
     */
    public function setImage(FileImage $image)
    {
        $this->image = $image;
        
        return $this;
    }
    
    /**
     * Set description
     * 
     * @param string $description
     * 
     * @return Image
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Set author
     * 
     * @param User $author
     * 
     * @return Image
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
        
        return $this;
    }

    /**
     * Set time
     * 
     * @param \DateTime $time
     * 
     * @return Image
     */
    public function setTime(\DateTime $time = null)
    {
        $this->time = $time;
        
        return $this;
    }
    
    /**
     * Set updatedAt
     * 
     * @param \DateTime $updatedAt
     * 
     * @return Image
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    /**
     * Set entry
     * 
     * @param Entry $entry
     * 
     * @return Image
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        
        return $this;
    }

    /**
     * Checks if the author is valid. i.e. he isn't deleted
     * 
     * @return boolean
     */
    public function hasValidAuthor()
    {
        try {
            return $this->author->id;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns a displayable author. Performs an exists check
     * 
     * @return string|User
     */
    public function getAuthorDisplay()
    {
        return $this->hasValidAuthor() ? $this->getAuthor() : '?';
    }
}
