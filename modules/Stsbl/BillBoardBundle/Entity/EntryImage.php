<?php declare(strict_types = 1);

namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use IServ\CoreBundle\Model\FileImage;
use IServ\CoreBundle\Util\Date;
use IServ\CrudBundle\Entity\CrudInterface;

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
     * @ORM\Column(name="description", type="text", nullable=true)
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
    public function onCreate()/*: void*/
    {
        $this->setTime(Date::now());
        $this->updateLastUpdatedTime();
    }
    
    /**
     * Lifecycle callback to set the update date
     *
     * @ORM\PreUpdate
     */
    public function onUpdate()/*: void*/
    {
        $this->updateLastUpdatedTime();
    }

    /**
     * Updates last updated time to 'now'
     */
    public function updateLastUpdatedTime()/*: void*/
    {
        $this->setUpdatedAt(Date::now());
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getImage()->getFileName() ? $this->getImage()->getFileName() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getImage(): ?FileImage
    {
        return $this->image;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getTime(): ?\DateTime
    {
        return $this->time;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }
    
    /**
     * @return $this
     */
    public function setImage(FileImage $image = null): self
    {
        $this->image = $image;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setDescription(string $description = null): self
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setAuthor(User $author = null): self
    {
        $this->author = $author;
        
        return $this;
    }

    /**
     * @return $this
     */
    public function setTime(\DateTime $time = null): self
    {
        $this->time = $time;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }
    
    /**
     * @return $this
     */
    public function setEntry(Entry $entry = null): self
    {
        $this->entry = $entry;
        
        return $this;
    }

    /**
     * Checks if the author is valid. i.e. he isn't deleted.
     */
    public function hasValidAuthor(): bool
    {
        return $this->author !== null;
    }

    /**
     * Returns a displayable author. Performs an exists check.
     */
    public function getAuthorDisplay(): string
    {
        return $this->hasValidAuthor() ? (string)$this->getAuthor() : '?';
    }

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
