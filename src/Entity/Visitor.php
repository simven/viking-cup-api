<?php

namespace App\Entity;

use App\Repository\VisitorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitorRepository::class)]
class Visitor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'visitors')]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'visitors')]
    private ?RoundDetail $roundDetail = null;

    #[ORM\Column]
    private ?int $companions = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $registrationDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getRoundDetail(): ?RoundDetail
    {
        return $this->roundDetail;
    }

    public function setRoundDetail(?RoundDetail $roundDetail): static
    {
        $this->roundDetail = $roundDetail;

        return $this;
    }

    public function getCompanions(): ?int
    {
        return $this->companions;
    }

    public function setCompanions(int $companions): static
    {
        $this->companions = $companions;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTime $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }
}
