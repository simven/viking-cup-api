<?php

namespace App\Entity;

use App\Repository\PenaltyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PenaltyRepository::class)]
class Penalty
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['penalty'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'penalties')]
    private ?PilotRoundCategory $pilotRoundCategory = null;

    #[ORM\ManyToOne(inversedBy: 'penalties')]
    #[Groups(['penaltyPenaltyReason'])]
    private ?PenaltyReason $penaltyReason = null;

    #[ORM\Column]
    #[Groups(['penalty'])]
    private ?int $points = null;

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

    public function getPenaltyReason(): ?PenaltyReason
    {
        return $this->penaltyReason;
    }

    public function setPenaltyReason(?PenaltyReason $penaltyReason): static
    {
        $this->penaltyReason = $penaltyReason;

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
}
