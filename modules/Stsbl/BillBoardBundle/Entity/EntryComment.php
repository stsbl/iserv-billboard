<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Entity;

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
 * @ORM\Table(name="billboard_comments")
 * @ORM\HasLifecycleCallbacks
 */
class EntryComment implements CrudInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id;

    /**
     * @ORM\Column(name="title",type="text",length=255)
     * @Assert\NotBlank()
     */
    private ?string $title;

    /**
     * @ORM\Column(name="content",type="text")
     * @Assert\NotBlank()
     */
    private ?string $content;

    /**
     * @ORM\ManyToOne(targetEntity="\IServ\CoreBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="author", referencedColumnName="act")
     */
    private ?User $author;

    /**
     * @ORM\Column(name="time",type="datetimetz_immutable", nullable=false)
     */
    private \DateTimeImmutable $time;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="comments")
     * @ORM\JoinColumn(name="entry", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private ?Entry $entry;

    public function __construct()
    {
        $this->time = Zeit::now();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * Returns a displayable author. Performs an exists check.
     */
    public function getAuthorDisplay(): string
    {
        return $this->hasValidAuthor() ? (string)$this->getAuthor() : '?';
    }

    public function getEntry(): ?Entry
    {
        return $this->entry;
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
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
    public function setContent(string $content = null): self
    {
        $this->content = $content;

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
