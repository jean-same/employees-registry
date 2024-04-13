<?php

namespace App\Controller\Api\V1;

use App\Entity\Person;
use App\Entity\Employment;
use FOS\RestBundle\View\View;
use OpenApi\Attributes as OA;
use App\Repository\PersonRepository;
use App\Service\SerializationService;
use App\Service\EntityValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

#[Rest\Route('/v1/persons', name: 'api_v1_person_')]
class PersonController extends AbstractFOSRestController
{
    public function __construct(
        private SerializationService $serializationService,
        private EntityManagerInterface $em,
        private EntityValidatorService $entityValidatorService,
        private PersonRepository $personRepository,
    ) { }

   
    #[Rest\Get('', name: 'browse'),
        OA\Response(
            response: Response::HTTP_OK,
            description: 'Success',
            content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'message', type: 'string', description: 'OK', example: 'Ok'),
                new OA\Property(
                    property: 'data',
                    type: 'object',
                    description: 'Object containing the data',
                    example: [
                        'message' => 'Ok',
                        'data' => [
                            'id' => 1,
                            'name' => 'John Doe',
                            'age' => 30,
                            'dateOfBirth' => '1900-02-14T00:00:00+00:00',
                            'employment' => [
                                [
                                    'id' => 1,
                                    'position' => 'Developer back-end',
                                    'startDate' => '2021-01-01T00:00:00+00:00',
                                    'endDate' => '2021-12-31T00:00:00+00:00',
                                    'companyName' => 'Company A',
                                    'isCurrent' => false
                                ]
                            ]
                        ]
                    ]
                ),
            ]),
        )
    ]
    #[Rest\View(serializerGroups: ['person:read'])]
    public function browse(): View
    {
        $personsWithTheirCurrentEmployment = $this->personRepository->findAllPersonWithCurrentEmployment();

        return $this->view([
            'message' => 'Ok',
            'data' => $personsWithTheirCurrentEmployment
        ], Response::HTTP_OK);
    }

    #[Rest\Post('', name: 'add'),
    OA\RequestBody(
        description: 'Person to add',
        required: true,
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(
                property: 'firstName',
                type: 'string',
                description: 'First name of the person',
                example: 'John',
            ),
            new OA\Property(
                property: 'lastName',
                type: 'string',
                description: 'Last name of the person',
                example: 'Doe',
            ),
            new OA\Property(
                property: 'dateOfBirth',
                type: 'string',
                description: 'Date of birth of the person',
                example: '1900-02-14',
            ),
        ]),
    ),
    OA\Response(
        response: Response::HTTP_OK,
        description: 'Success',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', description: 'OK', example: 'Ok'),
            new OA\Property(
                property: 'data',
                type: 'object',
                description: 'Object containing the data',
                example: [
                    'message' => 'Person added successfully',
                    'data' => [
                        'id' => 1,
                        'name' => 'John Doe',
                        'age' => 30,
                        'dateOfBirth' => '1900-02-14T00:00:00+00:00',
                        'employment' => []
                    ]
                ]
            ),
        ]),
    )
    ]
    #[Rest\View(serializerGroups :[ "person:write" ])]
    public function add(Request $request): View
    {

        $person = $this->serializationService->deserializeRequest($request->getContent(), Person::class);

        $this->entityValidatorService->validateAndPersistEntity($person);

        return $this->view([
            'message' => 'Person added successfully',
            'data' => $person
        ], Response::HTTP_OK);
    }

    #[Rest\Post('/{id}/add-employment', name: 'add_employment'),
    OA\RequestBody(
        description: 'Employment to add',
        required: true,
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(
                property: 'position',
                type: 'string',
                description: 'Position of the employment',
                example: 'Developer back-end',
            ),
            new OA\Property(
                property: 'startDate',
                type: 'string',
                description: 'Start date of the employment',
                example: '2021-01-01',
            ), 
            new OA\Property(
                property: 'endDate',
                type: 'string',
                description: 'End date of the employment',
                example: '2021-12-31',
            ),
            new OA\Property(
                property: 'companyName',
                type: 'string',
                description: 'Company name of the employment',
                example: 'Company A',
            ),
            new OA\Property(
                property: 'isCurrent',
                type: 'boolean',
                description: 'Is the employment current',
                example: false,
            )
        ]),
    ),
    OA\Response(
        response: Response::HTTP_OK,
        description: 'Success',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', description: 'OK', example: 'Ok'),
            new OA\Property(
                property: 'data',
                type: 'object',
                description: 'Object containing the data',
                example: [
                    'name' => 'John Doe',
                    'age' => 30,
                    'dateOfBirth' => '1900-02-14T00:00:00+00:00',
                    'employment' => [
                        [
                            'id' => 1,
                            'position' => 'Developer back-end',
                            'startDate' => '2021-01-01T00:00:00+00:00',
                            'endDate' => '2021-12-31T00:00:00+00:00',
                            'companyName' => 'Company A',
                            'isCurrent' => false
                        ]
                    ]
                ]
            ),
        ]),
    )]
    #[Rest\View(serializerGroups :[ "person:write" ])]
    public function addEmployment(Person $person ,Request $request): View
    {
        $employment = $this->serializationService->deserializeRequest($request->getContent(), Employment::class);

        $employment->validateForAdd();

        $employment->addPerson($person);

        $this->entityValidatorService->validateAndPersistEntity($employment);

        return $this->view([
            'message' => 'Employment added successfully',
            'data' => $person
        ], Response::HTTP_OK);
    }

    #[Rest\Get('/{companyName}', name: 'by_company'),
    OA\Response(
        response: Response::HTTP_OK,
        description: 'Success',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', description: 'OK', example: 'Ok'),
            new OA\Property(
                property: 'data',
                type: 'object',
                description: 'Array containing the data',
                example: [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'age' => 30,
                        'dateOfBirth' => '1900-02-14T00:00:00+00:00',
                        'employment' => [
                            [
                                'id' => 1,
                                'position' => 'Developer back-end',
                                'startDate' => '2021-01-01T00:00:00+00:00',
                                'endDate' => '2021-12-31T00:00:00+00:00',
                                'companyName' => 'Company A',
                                'isCurrent' => false
                            ]
                        ]
                    ]
                ]
            ),
        ]),
    )
    ]
    #[Rest\View(serializerGroups :[ "person:read" ])]
    public function personByCompany(string $companyName): View
    {
        $people = $this->personRepository->findPersonByCompanyName(strtoupper($companyName));

        return $this->view([
            'message' => 'Ok',
            'data' => $people
        ], Response::HTTP_OK);
    }

    #[Rest\Get('/{id}/employments', name: 'employments'),
    OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'The id of the person',
        schema: new OA\Schema(type: 'integer')
    ),
    OA\Parameter(name: 'startDate', in: 'query', description: 'The start date of the range', required: true, schema: new OA\Schema(type: 'date', format: 'Y-m-d')),
    OA\Parameter(name: 'endDate', in: 'query', description: 'The end date of the range', required: false, schema: new OA\Schema(type: 'date', format: 'Y-m-d')),
    OA\Response(
        response: Response::HTTP_OK,
        description: 'Success',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'message', type: 'string', description: 'OK', example: 'Ok'),
            new OA\Property(
                property: 'data',
                type: 'object',
                description: 'Array containing the data',
                example: [
                    [
                        'id' => 1,
                        'position' => 'Developer back-end',
                        'startDate' => '2021-01-01T00:00:00+00:00',
                        'endDate' => '2021-12-31T00:00:00+00:00',
                        'companyName' => 'Company A',
                        'isCurrent' => false
                    ]
                ]
            ),
        ]),
    )
    ]
    #[Rest\View(serializerGroups :[ "person:read" ])]
    public function personEmploymentsDateRange(Request $request, Person $person): View
    {
        $startDate = new \DateTimeImmutable($request->query->get('startDate'));
        $endDate = new \DateTimeImmutable($request->query->get('endDate')); 

        if ($startDate > $endDate) {
            throw new BadRequestException('The start date must be before the end date.');
        }

        $employments = $this->personRepository->findEmploymentsByPersonAndDateRange($person, $startDate, $endDate);

        return $this->view([
            'message' => 'Ok',
            'data' => $employments
        ], Response::HTTP_OK);
    }
}
