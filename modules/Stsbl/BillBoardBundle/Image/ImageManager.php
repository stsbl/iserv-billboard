<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Image;

use Doctrine\ORM\EntityManagerInterface;
use IServ\Library\Image\Exception\ImageException;
use IServ\Library\Image\Image;
use IServ\Library\Uuid\Uuid;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\MimeTypes;
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


        if (false === $file->getSize()) {
            throw new \RuntimeException('Could not read size of file!');
        }
        if (false === $mimetype = $file->getMimetype()) {
            throw new \RuntimeException('Could not get mimetype for file!');
        }
        if (!MimeTypes::getDefault()->getExtensions($mimetype)) {
            throw new \RuntimeException('Could not get extension for mimetype: ' . $mimetype);
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

    public function convertFileImage(EntryImage $image): void
    {
        try {
            Assert::notNull($fileImage = $image->getImage());
        } catch (InvalidArgumentException $e) {
            throw new \RuntimeException('Invalid file image within entry image: ' . $e->getMessage(), previous: $e);
        }

        $image->setImageName($fileImage->getFileName());
        $image->setImage(null);
        $this->createFile($fileImage->getData(), $image);
        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }

    public function delete(EntryImage $image): void
    {
        try {
            Assert::string($id = $image->getImageUuid());
        } catch (InvalidArgumentException) {
            return;
        }

        $this->filesystem->remove($this->buildFilePath($id));
        $this->entityManager->remove($image);
        $this->entityManager->flush();
    }

    public function path(EntryImage $image): string
    {
        return $this->buildFilePath($image->getImageUuid());
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

        $entity->setImageUuid($uuid->toNormalizedString());
    }

}
