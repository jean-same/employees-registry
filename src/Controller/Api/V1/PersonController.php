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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     /**
     * Retrieves all persons with their current employment.
     * 
     * @return View The view containing the response
     */
    public function browse(): View
    {
        return $this->view([
            'message' => 'Ok',
            'data' => $this->personRepository->findAllPersonWithCurrentEmployment()
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
    /**
     * Adds a new person.
     *
     * @param Request $request The HTTP request
     * 
     * @return View The view containing the response
     */
    public function add(Request $request): View
    {

        // Deserialize the request content into a Person object and validate/persist it.
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
    /**
     * Adds a new employment for the specified person.
     *
     *
     * @param Person $person The person to add the employment for
     * @param Request $request The HTTP request
     * 
     * @throws NotFoundHttpException If the person is not found
     * 
     * @return View The view containing the response
     */
    public function addEmployment(Person $person ,Request $request): View
    {
        // Deserialize the request content into an Employment object, validate it for addition
        $employment = $this->serializationService->deserializeRequest($request->getContent(), Employment::class);
        $employment->validateForAdd();

        // add the person to the employment, and validate and persist the employment entity.
        $employment->addPerson($person);
        $this->entityValidatorService->validateAndPersistEntity($employment);

        return $this->view([
            'message' => 'Employment added successfully',
            'data' => $person
        ], Response::HTTP_OK);
    }

    #[Rest\Get('/by-company', name: 'by_company'),
    OA\Parameter(name: 'companyName', in: 'query', description: 'Name of the company', required: true, schema: new OA\Schema(type: 'string')),
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
    /**
     * Retrieves a list of people associated with a given company name.
     *
     * This method handles requests to fetch people associated with a specific company.
     * It validates the incoming request parameters and queries the repository to find
     * people associated with the provided company name.
     *
     * @param Request $request The HTTP request object containing query parameters.
     *
     * @return View The JSON response containing the list of people associated with the company.
     *
     * @throws NotFoundHttpException When the company name is not provided in the request.
     */
    public function personByCompany(Request $request): View
    {
        $companyName = $request->query->get('companyName');

        if (!$companyName) {
            throw new NotFoundHttpException('Company name not provided');
        }

        $people = $this->personRepository->findPersonByCompanyName($companyName);

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
    /**
     * Retrieves employments for a person within a specified date range.
     *
     *
     * @param Request $request The HTTP request object containing query parameters.
     * @param Person $person The Person entity for whom employments are to be retrieved.
     * @return View A view containing the employments data.
     *
     * @throws BadRequestHttpException If the start date is missing, invalid, or provided in an unexpected format.
     * @throws BadRequestHttpException If the end date is provided but invalid or in an unexpected format.
     * @throws BadRequestException If the start date is after the end date.
     */
    public function personEmploymentsDateRange(Request $request, Person $person): View
    {
        $startDateString = $request->query->get('startDate');
        $endDateString = $request->query->get('endDate');

        $startDate = $this->validateStartDate($startDateString);

        $endDate = $this->validateEndDate($endDateString);

        if ($endDate && $startDate > $endDate) {
            throw new BadRequestException('The start date must be before the end date.');
        }

        $employments = $this->personRepository->findEmploymentsByPersonAndDateRange($person, $startDate, $endDate);

        return $this->view([
            'message' => 'Ok',
            'data' => $employments
        ], Response::HTTP_OK);
    }
    
    private function validateStartDate(string $startDateString): \DateTimeImmutable
    {
        if (!$startDateString) {
            throw new BadRequestHttpException('Start date is required.');
        }

        $startDate = \DateTimeImmutable::createFromFormat('Y-m-d', $startDateString);

        if (!$startDate) {
            throw new BadRequestHttpException('Invalid start date format provided.');
        }

        return $startDate;
    }

    private function validateEndDate(string $endDateString): ?\DateTimeImmutable
    {
        if (!$endDateString) {
            return null;
        }

        $endDate = \DateTimeImmutable::createFromFormat('Y-m-d', $endDateString);

        if (!$endDate) {
            throw new BadRequestHttpException('Invalid end date format provided.');
        }

        return $endDate;
    }
}
