<?php

namespace App\Entity;

use ErrorException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['person:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(min: 2, max: 254)]
    #[Assert\NotNull(message: "The first name can not be null.")]
    #[Groups(['person:read', 'person:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 128)]
    #[Assert\Length(min: 2, max: 254)]
    #[Assert\NotNull(message: "The last name can not be null.")]
    #[Groups(['person:read', 'person:write'])]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: "The date of birth can not be null.")]
    #[Groups(['person:read', 'person:write'])]
    private ?\DateTimeImmutable $dateOfBirth = null;

    #[Groups(['person:read'])]
    private ?int $age = null;

    #[ORM\ManyToMany(targetEntity: Employment::class, inversedBy: 'people')]
    #[Groups(['person:read', 'person:write'])]
    private Collection $employment;

    public function __construct()
    {
        $this->employment = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeImmutable $dateOfBirth): static
    {

        $now = new \DateTimeImmutable();
        $age = $now->diff($dateOfBirth)->y;

        if ($age >= 150) {
            throw new ErrorException('The person must be under 150 years old.');
        }

        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getAge(): ?int
    {
        $dateOfBirth = $this->getDateOfBirth();
        if (!$dateOfBirth) {
            return null;
        }

        $currentDate = new \DateTime();
        $this->age = $dateOfBirth->diff($currentDate)->y;
        
        return $this->age;
    }

    /**
     * @return Collection<int, Employment>
     */
    public function getEmployment(): Collection
    {
        return $this->employment;
    }

    public function addEmployment(Employment $employment): static
    {
        if (!$this->employment->contains($employment)) {
            $this->employment->add($employment);
        }

        return $this;
    }

    public function removeEmployment(Employment $employment): static
    {
        $this->employment->removeElement($employment);

        return $this;
    }
}
