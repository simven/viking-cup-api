<?php

namespace App\Entity;

use App\Repository\QualifyingDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QualifyingDetailRepository::class)]
class QualifyingDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qualifyingDetail'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'qualifyingDetails')]
    private ?Qualifying $qualifying = null;

    #[ORM\ManyToOne(inversedBy: 'qualifyingDetails')]
    #[Groups(['qualifyingDetailCriteria'])]
    private ?QualifyingCriteria $qualifyingCriteria = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['qualifyingDetail'])]
    private ?int $points = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['qualifyingDetail'])]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQualifying(): ?Qualifying
    {
        return $this->qualifying;
    }

    public function setQualifying(?Qualifying $qualifying): static
    {
        $this->qualifying = $qualifying;

        return $this;
    }

    public function getQualifyingCriteria(): ?QualifyingCriteria
    {
        return $this->qualifyingCriteria;
    }

    public function setQualifyingCriteria(?QualifyingCriteria $qualifyingCriteria): static
    {
        $this->qualifyingCriteria = $qualifyingCriteria;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): static
    {
        $this->points = $points;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
