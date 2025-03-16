<?php

namespace App\Entity;

use App\Repository\QualifyingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QualifyingRepository::class)]
class Qualifying
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qualifying'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'qualifyings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PilotRoundCategory $pilotRoundCategory = null;

    #[ORM\Column]
    #[Groups(['qualifying'])]
    private ?int $points = null;

    #[ORM\Column]
    #[Groups(['qualifying'])]
    private ?int $passage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPilotRoundCategory(): ?PilotRoundCategory
    {
        return $this->pilotRoundCategory;
    }

    public function setPilotRoundCategory(?PilotRoundCategory $pilotRoundCategory): static
    {
        $this->pilotRoundCategory = $pilotRoundCategory;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getPassage(): ?int
    {
        return $this->passage;
    }

    public function setPassage(int $passage): static
    {
        $this->passage = $passage;

        return $this;
    }
}
