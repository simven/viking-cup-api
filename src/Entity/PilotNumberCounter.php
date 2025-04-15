<?php

namespace App\Entity;

use App\Repository\PilotNumberCounterRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PilotNumberCounterRepository::class)]
class PilotNumberCounter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $pilotNumberCounter = null;

    #[ORM\OneToOne(inversedBy: 'pilotNumberCounter', cascade: ['persist', 'remove'])]
    private ?Category $category = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPilotNumberCounter(): ?int
    {
        return $this->pilotNumberCounter;
    }

    public function setPilotNumberCounter(int $pilotNumberCounter): static
    {
        $this->pilotNumberCounter = $pilotNumberCounter;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
