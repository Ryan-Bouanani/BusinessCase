<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;

class BaseTestCase extends KernelTestCase
{
    protected function assertValidationErrorsCount($entity, $expectedCount)
    {
        $errors = self::getContainer()->get('validator')->validate($entity);
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        };
        $this->assertCount($expectedCount, $errors, implode(', ', $messages));
    }
}