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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Rest\Route('/v1/persons', name: 'api_v1_person_')]
class PersonController extends AbstractFOSRestController
{
    public function __construct(
        private SerializationService $serializationService,
        private EntityManagerInterface $em,
        private EntityValidatorService $entityValidatorService,
        private PersonRepository $personRepository,
    ) { }

    #[Rest\Get('', name: 'browse')]
    #[Rest\View(serializerGroups: ['person:read'])]
    public function browse(): View
    {
        $personsWithTheirCurrentEmployment = $this->personRepository->findAllPersonWithCurrentEmployment();

        return $this->view([
            'message' => 'Ok',
            'data' => $personsWithTheirCurrentEmployment
        ], Response::HTTP_OK);
    }

    #[Rest\Post('', name: 'add')]
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

    #[Rest\Post('/{id}/add-employment', name: 'add_employment')]
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

    #[Rest\Get('/{companyName}', name: 'by_company')]
    #[Rest\View(serializerGroups :[ "person:write" ])]
    public function personByCompany(string $companyName): View
    {
        $people = $this->personRepository->findPersonByCompanyName(strtoupper($companyName));

        return $this->view([
            'message' => 'Ok',
            'data' => $people
        ], Response::HTTP_OK);
    }

    #[Rest\Get('/{id}/employments', name: 'employments')]
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
