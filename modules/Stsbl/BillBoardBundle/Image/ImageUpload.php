<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Image;

use IServ\FilesystemBundle\Model\File;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Stsbl\BillBoardBundle\Validator\Constraints\SupportedImage;
use Symfony\Component\Validator\Constraints as Assert;

final class ImageUpload
{
    /**
     * @SupportedImage()
     * @Assert\NotBlank(message="Please upload an image.")
     */
    private ?File $image = null;

    public function __construct(
        private readonly EntryImage $entity,
    ) {
    }

    public function setDescription(?string $description): void
    {
        $this->entity->setDescription($description);
    }

    public function getDescription(): ?string
    {
        return $this->entity->getDescription();
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image): void
    {
        $this->image = $image;
        $this->entity->setImageName($image?->getName());
    }

    public function getEntity(): EntryImage
    {
        return $this->entity;
    }
}
