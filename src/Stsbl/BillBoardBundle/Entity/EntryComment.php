<?php
// src/Stsbl/BillBoardBunle/Entity/EntryComment.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use IServ\CrudBundle\Entity\CrudInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
 * BillBoardBundle:EntryComment
 *
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 * @ORM\Entity
 * @ORM\Table(name="billboard_comments")
 * @ORM\HasLifecycleCallbacks
 */
class EntryComment implements CrudInterface
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
     * @ORM\Column(name="content",type="text")
     * @Assert\NotBlank()
     * 
     * @var string
     */
    private $content;
    
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
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="comments")
     * @ORM\JoinColumn(name="entry", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @var Entry
     */
    private $entry;
    
    /**
     * Returns a human readable string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->title;
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
     * Get content
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
     * Returns a displayable author. Performs an exists check
     * 
     * @return string|User
     */
    public function getAuthorDisplay()
    {
        return $this->hasValidAuthor() ? $this->getAuthor() : '?';
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
     * Get time
     * 
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set title
     * 
     * @param string $title
     * 
     * @return EntryComment
     */
    public function setTitle($title)
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * Set content
     * 
     * @param string $content
     * 
     * @return EntryComment
     */
    public function setContent($content)
    {
        $this->content = $content;
        
        return $this;
    }
    
    /**
     * Set author
     * 
     * @param User $author
     * 
     * @return EntryComment
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
     * @return EntryComment
     */
    public function setTime(\DateTime $time = null)
    {
        $this->time = $time;
        
        return $this;
    }
    
    /**
     * Set entry
     * 
     * @param Entry §entry
     * 
     * @return EntryComment
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        
        return $this;
    }

    /**
     * Lifecycle callback to set the creation date
     *
     * @ORM\PrePersist
     */
    public function onCreate()
    {
        $this->setTime(new \DateTime("now"));
    }
    
    /**
     * Checks if the author is valid. i.e. he isn't deleted
     * 
     * @return boolean
     */
    public function hasValidAuthor()
    {
        return $this->author != null;
    }
}
