<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class SupportedImage extends Constraint
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getMessage(): string
    {
        return _('The given file has a not supported format.');
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
