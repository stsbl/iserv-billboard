<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use IServ\CoreBundle\Entity\User;
use IServ\CrudBundle\Entity\CrudInterface;
use IServ\Library\Uuid\UuidInterface;
use IServ\Library\Zeit\Zeit;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

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
 * @ORM\Table(name="billboard_images")
 * @ORM\HasLifecycleCallbacks
 */
class EntryImage implements CrudInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(name="image_uuid", type="iserv_uuid", nullable=false)
     */
    private ?UuidInterface $imageUuid = null;

    /**
     * @ORM\Column(name="image_name", type="text", nullable=false)
     */
    private ?string $imageName = null;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="author", referencedColumnName="act")
     */
    private ?User $author;

    /**
     * @ORM\Column(name="time",type="datetimetz_immutable",nullable=false)
     */
    private \DateTimeImmutable $time;

    /**
     * @ORM\Column(name="updated_at",type="datetimetz_immutable",nullable=false)
     */
    private \DateTimeImmutable $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="images")
     * @ORM\JoinColumn(name="entry", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private ?Entry $entry;

    public function __construct()
    {
        $this->time = Zeit::now();
        $this->updateLastUpdatedTime();
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
        return $this->imageName ?? '?';
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    public function getImageUuid(): UuidInterface
    {
        try {
            Assert::notNull($this->imageUuid, 'UUID not set. Did you forgot to set the UUID using setImageUuid()?');
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException($e->getMessage(), previous: $e);
        }

        return $this->imageUuid;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function setImageUuid(UuidInterface $imageUuid): void
    {
        $this->imageUuid = $imageUuid;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
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
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return $this
     */
    public function setEntry(Entry $entry): self
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
