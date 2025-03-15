<?php

namespace App\Entity;

use App\Repository\BattleVersusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleVersusRepository::class)]
class BattleVersus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $pilotQualifPosition1 = null;

    #[ORM\Column]
    private ?int $pilotQualifPosition2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPilotQualifPosition1(): ?int
    {
        return $this->pilotQualifPosition1;
    }

    public function setPilotQualifPosition1(int $pilotQualifPosition1): static
    {
        $this->pilotQualifPosition1 = $pilotQualifPosition1;

        return $this;
    }

    public function getPilotQualifPosition2(): ?int
    {
        return $this->pilotQualifPosition2;
    }

    public function setPilotQualifPosition2(int $pilotQualifPosition2): static
    {
        $this->pilotQualifPosition2 = $pilotQualifPosition2;

        return $this;
    }
}
