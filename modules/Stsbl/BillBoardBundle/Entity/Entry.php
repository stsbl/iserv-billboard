<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use IServ\CoreBundle\Util\Date;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\Library\Zeit\Zeit;
use Symfony\Component\Validator\Constraints as Assert;

/*
 * The MIT License
 *
 * Copyright 2021 Felix Jacobi.
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
 * @ORM\Table(name="billboard")
 * @ORM\HasLifecycleCallbacks
 */
class Entry implements CrudInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(name="title", type="text")
     * @Assert\NotBlank()
     */
    private ?string $title;

    /**
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private ?string $description;

    /**
     * @ORM\Column(name="time", type="datetimetz_immutable", nullable=false)
     */
    private \DateTimeImmutable $time;

    /**
     * @ORM\Column(name="updated_at",type="datetimetz_immutable",nullable=false)
     */
    private \DateTimeImmutable $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="\Stsbl\BillBoardBundle\Entity\Category", fetch="EAGER")
     * @ORM\JoinColumn(name="category", referencedColumnName="id")
     * @Assert\NotNull()
     */
    private ?Category $category;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="author", referencedColumnName="act")
     */
    private ?User $author;

    /**
     * @ORM\Column(name="visible", type="boolean")
     */
    private bool $visible = true;

    /**
     * @ORM\Column(name="closed", type="boolean")
     */
    private bool $closed = false;

    /**
     * @ORM\OneToMany(targetEntity="EntryImage", mappedBy="entry")
     *
     * @var Collection&EntryImage[]
     */
    private Collection $images;

    /**
     * @ORM\OneToMany(targetEntity="EntryComment", mappedBy="entry")
     *
     * @var Collection&EntryComment[]
     */
    private Collection $comments;

    /**
     * The constructor
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->time = Zeit::now();
        $this->updatedAt = Zeit::now();
    }


    /**
     * Lifecycle callback to set the update date
     *
     * @ORM\PreUpdate
     */
    public function onUpdate(): void
    {
        $this->updateLastUpdatedTime();
    }

    /**
     * Updates last updated time to 'now'
     */
    public function updateLastUpdatedTime(): void
    {
        $this->setUpdatedAt(Zeit::now());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->title ?? '?';
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTime(): ?\DateTimeImmutable
    {
        return $this->time;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @return Collection&EntryImage[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    /**
     * @return Collection&EntryComment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return $this
     */
    public function setTitle(string $title = null): self
    {
        $this->title = $title;

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
    public function setTime(\DateTimeImmutable $time): self
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @return $this
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return $this
     */
    public function setCategory(Category $category = null): self
    {
        $this->category = $category;

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
    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

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

    /**
     * @return $this
     */
    public function setClosed(bool $closed): self
    {
        $this->closed = $closed;

        return $this;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @return $this
     */
    public function addImage(EntryImage $image): self
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeImage(EntryImage $image): self
    {
        $this->images->removeElement($image);

        return $this;
    }

    public function hasImage(EntryImage $image): bool
    {
        return $this->images->contains($image);
    }

    /**
     * @return $this
     */
    public function addComment(EntryComment $comment): self
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeComment(EntryComment $comment): self
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    public function hasComment(EntryComment $comment): bool
    {
        return $this->comments->contains($comment);
    }
}
