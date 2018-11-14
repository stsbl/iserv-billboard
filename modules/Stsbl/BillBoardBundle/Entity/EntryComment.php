<?php declare(strict_types = 1);
// src/Stsbl/BillBoardBunle/Entity/EntryComment.php
namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use IServ\CoreBundle\Util\Date;
use IServ\CrudBundle\Entity\CrudInterface;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * The MIT License
 *
 * Copyright 2018 Felix Jacobi.
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
 * @author Felix Jacobi <felix.jacobi@stsbl.de>
 * @license MIT license <https://opensource.org/licenses/MIT>
 *
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
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()/*: ?int*/
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getTitle()/*: ?string*/
    {
        return $this->title;
    }
    
    /**
     * @return string
     */
    public function getContent()/*: ?string*/
    {
        return $this->content;
    }
    
    /**
     * @return User
     */
    public function getAuthor()/*: ?User*/
    {
        return $this->author;
    }

    /**
     * Returns a displayable author. Performs an exists check.
     *
     * @return string
     */
    public function getAuthorDisplay(): string
    {
        return $this->hasValidAuthor() ? (string)$this->getAuthor() : '?';
    }
    
    /**
     * @return Entry
     */
    public function getEntry()/*: ?Entry*/
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
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title = null): self
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * @param string $content
     * @return $this
     */
    public function setContent(string $content = null): self
    {
        $this->content = $content;
        
        return $this;
    }
    
    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor(User $author = null): self
    {
        $this->author = $author;
        
        return $this;
    }
    
    /**
     * @param \DateTime $time
     * @return $this
     */
    public function setTime(\DateTime $time = null): self
    {
        $this->time = $time;
        
        return $this;
    }
    
    /**
     * @param Entry §entry
     * @return $this
     */
    public function setEntry(Entry $entry): self
    {
        $this->entry = $entry;
        
        return $this;
    }

    /**
     * Lifecycle callback to set the creation date
     *
     * @ORM\PrePersist
     */
    public function onCreate()/*: void*/
    {
        $this->setTime(Date::now());
    }
    
    /**
     * Checks if the author is valid. i.e. he isn't deleted.
     *
     * @return bool
     */
    public function hasValidAuthor(): bool
    {
        return $this->author !== null;
    }

    /**
     * @param Entry $entry
     * @param User $user
     * @return self
     */
    public static function createForEntryAndUser(Entry $entry, User $user): self
    {
        $instance = new self();

        $instance
            ->setEntry($entry)
            ->setAuthor($user)
        ;

        return $instance;
    }
}
