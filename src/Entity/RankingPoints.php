<?php

namespace App\Entity;

use App\Repository\RankingPointsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RankingPointsRepository::class)]
class RankingPoints
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $entity = null;

    #[ORM\Column]
    private ?int $fromPosition = null;

    #[ORM\Column]
    private ?int $toPosition = null;

    #[ORM\Column]
    private ?int $points = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getFromPosition(): ?int
    {
        return $this->fromPosition;
    }

    public function setFromPosition(int $fromPosition): static
    {
        $this->fromPosition = $fromPosition;

        return $this;
    }

    public function getToPosition(): ?int
    {
        return $this->toPosition;
    }

    public function setToPosition(int $toPosition): static
    {
        $this->toPosition = $toPosition;

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
