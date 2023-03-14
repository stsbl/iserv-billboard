<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use IServ\Library\Logger\ModuleLogger;
use Psr\Log\LoggerInterface;
use Stsbl\BillBoardBundle\Entity\EntryImage;
use Stsbl\BillBoardBundle\Image\ImageManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MigrateImagesCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'stsbl:billboard:migrate-images';

    /**
     * {@inheritDoc}
     */
    protected static $defaultDescription = 'Converts FileImage from database into image files on disk.';

    private LoggerInterface $logger;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ImageManager $imageManager,
        LoggerInterface $logger,
    ) {
        $this->logger = new ModuleLogger('Bill-Board', $logger);

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $error = false;

        $qb = $this->entityManager->getRepository(EntryImage::class)->createQueryBuilder('i');

        $qb->select('i')
            ->where('i.image IS NOT NULL');

        foreach ($qb->getQuery()->toIterable() as $image) {
            try {
                $this->imageManager->convertFileImage($image);
            } catch (\Throwable $e) {
                $error = true;
                $this->logger->critical('Could not convert "{image}" from FileImage to file: {message}', ['image' => $image, 'exception' => $e, 'message' => $e->getMessage()]);
            }
        }

        return $error ? self::FAILURE : self::SUCCESS;
    }
}
