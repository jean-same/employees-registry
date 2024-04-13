<?php

namespace App\Service;

use BBOnline\Entity\Cinema\Screen;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityValidatorService
{

    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $em
        )
    {
    }

    /**
     * Validates an entity object against the defined constraints.
     *
     * @param object $value The entity object to validate.
     * @param mixed $constraints (Optional) The constraints to apply for validation.
     * @param mixed $groups (Optional) The validation groups to use.
     * @return array|false An array of validation errors if any, otherwise false.
     */
    private function validate($value, $constraints = null, $groups = null)
    {
        $violations = $this->validator->validate($value, $constraints, $groups);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $property = $violation->getPropertyPath();
                $message = $violation->getMessage();
                $errors[$property][] = $message;
            }

            return $errors;

        }

        return false;
    }

    /**
     * Validates and persists an entity object.
     *
     * @param object $entityToValidate The entity object to validate and persist.
     * @param bool $update (Optional) Indicates if the operation is an update.
     * @return void
     * @throws UnprocessableEntityHttpException When validation fails.
     * @throws Exception When an error occurs during the persistence process.
     */
    public function validateAndPersistEntity(object $entityToValidate, bool $update = false): void
    {
        $violations = $this->validate($entityToValidate);

        if($violations) {
            throw new UnprocessableEntityHttpException(json_encode($violations));
        }

        try {
            if(!$update) {
                $this->em->persist($entityToValidate);
            }
            
            $this->em->flush();
        } catch (\Doctrine\DBAL\Exception\NotNullConstraintViolationException $e) {
            $notNullErrorMessage = strtolower($e->getMessage());

            if (strpos($notNullErrorMessage, 'not null violation') !== false) {

                $pattern = '/colonne « (.*?) »/';
                $columnName = null;

                if (preg_match($pattern, $notNullErrorMessage, $matches)) {
                    $columnName = $matches[1];
                } 

                $message = $columnName ? 'The field ' . $columnName . ' can not be null.' : 'At least one of the required fields is null.'; 

                throw new UnprocessableEntityHttpException(json_encode([
                    'error' => 'Not null violation',
                    'message' => $message
                ]));

            } else {
                throw new Exception($e->getMessage());
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}
