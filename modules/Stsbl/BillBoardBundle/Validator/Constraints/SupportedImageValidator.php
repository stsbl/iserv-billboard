<?php

declare(strict_types=1);

namespace Stsbl\BillBoardBundle\Validator\Constraints;

use IServ\FilesystemBundle\Model\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class SupportedImageValidator extends ConstraintValidator
{
    private const MIME_TYPES = ['image/gif', 'image/jpeg', 'image/png', 'image/webp'];

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SupportedImage) {
            throw new UnexpectedValueException($constraint, SupportedImage::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof File) {
            throw new UnexpectedValueException($value, File::class);
        }

        if (!in_array($value->getMimetype(), self::MIME_TYPES, true)) {
            $this->context
                ->buildViolation($constraint->getMessage())
                ->atPath('image')
                ->addViolation()
            ;
        }
    }

}
