<?php

namespace App\Service\Validators;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Validate an object and return errors as an exception.
     *
     * @param object $object
     * @throws BadRequestHttpException
     */
    public function validateAndThrow(object $object): void
    {
        $errors = $this->validator->validate($object);

        if (count($errors) > 0) {
            dump('ValidatorService -> EXCEPTION:'.  json_encode($this->formatErrors($errors))); // todo debug in php bin/console server:dump
            throw new BadRequestHttpException(json_encode($this->formatErrors($errors)));
        }
    }

    /**
     * Format validation errors into an array.
     *
     * @param ConstraintViolationListInterface $errors
     * @return array
     */
    private function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $errorMessages = [];

        foreach ($errors as $error) {
            $errorMessages[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return $errorMessages;
    }


}