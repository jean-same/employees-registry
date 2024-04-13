<?php

namespace App\Entity;

use ErrorException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EmploymentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[ORM\Entity(repositoryClass: EmploymentRepository::class)]
class Employment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['person:read', 'person:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max: 254)]
    #[Assert\NotNull(message: "The position can not be null.")]
    #[Groups(['person:read', 'person:write'])]
    private ?string $position = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: "The start date can not be null.")]
    #[Groups(['person:read', 'person:write'])]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['person:read', 'person:write'])]
    #[Assert\Expression(
        "value === null || value >= this.getStartDate()",
        message: "End date cannot be less than the start date."
    )]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\ManyToMany(targetEntity: Person::class, mappedBy: 'employment')]
    private Collection $people;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 2, max: 254)]
    #[Assert\NotNull(message: "The company name can not be null.")]
    private ?string $companyName = null;

    #[ORM\Column]
    private ?bool $isCurrent = false;

    public function __construct()
    {
        $this->people = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->addEmployment($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            $person->removeEmployment($this);
        }

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function isCurrent(): ?bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): static
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

    public function validateForAdd(): void
    {
        if ($this->isCurrent() && $this->getEndDate() === null) {
            throw new NotFoundHttpException('The end date is required for current employment');
        }
    }
}
