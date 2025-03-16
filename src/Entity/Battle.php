<?php

namespace App\Entity;

use App\Repository\BattleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleRepository::class)]
class Battle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'battles')]
    private ?PilotRoundCategory $pilotRoundCategory1 = null;

    #[ORM\ManyToOne(inversedBy: 'battles')]
    private ?PilotRoundCategory $pilotRoundCategory2 = null;

    #[ORM\ManyToOne]
    private ?PilotRoundCategory $winner = null;

    #[ORM\Column(nullable: true)]
    private ?int $passage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPilotRoundCategory1(): ?PilotRoundCategory
    {
        return $this->pilotRoundCategory1;
    }

    public function setPilotRoundCategory1(?PilotRoundCategory $pilotRoundCategory1): static
    {
        $this->pilotRoundCategory1 = $pilotRoundCategory1;

        return $this;
    }

    public function getPilotRoundCategory2(): ?PilotRoundCategory
    {
        return $this->pilotRoundCategory2;
    }

    public function setPilotRoundCategory2(?PilotRoundCategory $pilotRoundCategory2): static
    {
        $this->pilotRoundCategory2 = $pilotRoundCategory2;

        return $this;
    }

    public function getWinner(): ?PilotRoundCategory
    {
        return $this->winner;
    }

    public function setWinner(?PilotRoundCategory $winner): static
    {
        $this->winner = $winner;

        return $this;
    }

    public function getPassage(): ?int
    {
        return $this->passage;
    }

    public function setPassage(?int $passage): static
    {
        $this->passage = $passage;

        return $this;
    }
}
