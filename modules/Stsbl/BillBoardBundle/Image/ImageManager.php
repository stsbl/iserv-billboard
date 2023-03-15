<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Image;

use Doctrine\ORM\EntityManagerInterface;
use IServ\Library\Image\Exception\ImageException;
use IServ\Library\Image\Image;
use IServ\Library\Uuid\Uuid;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

final class ImageManager
{
    private const IMAGE_PATH = '/var/lib/stsbl/billboard/images';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function store(ImageUpload $upload): void
    {
        $file = $upload->getImage();

        try {
            Assert::notNull($file);
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException('Invalid file provided!', previous: $e);
        }

        try {
            Assert::string($content = $file->read());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException('Could not file content: ' . $e->getMessage(), previous: $e);
        }

        $entity = $upload->getEntity();

        $this->createFile($content, $entity);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function delete(EntryImage $image): void
    {
        $this->filesystem->remove($this->buildFilePath($image->getImageUuid()->toNormalizedString()));
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    public function path(EntryImage $image): string
    {
        return $this->buildFilePath($image->getImageUuid()->toNormalizedString());
    }

    private function buildFilePath(string $file): string
    {
        return self::IMAGE_PATH . DIRECTORY_SEPARATOR . $file . '.png';
    }

    private function createFile(string $content, EntryImage $entity): void
    {
        $uuid = Uuid::create();

        try {
            $image = Image::createFromString($content);
            $image->toPng()->save($this->buildFilePath($uuid->toNormalizedString()));
        } catch (ImageException $e) {
            throw new \RuntimeException('Could not convert image!', previous: $e);
        }

        $entity->setImageUuid($uuid);
    }

}
