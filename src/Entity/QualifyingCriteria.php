<?php

namespace App\Entity;

use App\Repository\QualifyingCriteriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: QualifyingCriteriaRepository::class)]
class QualifyingCriteria
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['qualifyingCriteria'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['qualifyingCriteria'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['qualifyingCriteria'])]
    private ?int $maxPoints = null;

    #[ORM\Column]
    #[Groups(['qualifyingCriteria'])]
    private ?bool $isBonus = null;

    /**
     * @var Collection<int, Qualifying>
     */
    #[ORM\OneToMany(targetEntity: Qualifying::class, mappedBy: 'qualifyingCriteria')]
    #[Groups(['qualifyingCriteriaQualifyings'])]
    private Collection $qualifyings;

    /**
     * @var Collection<int, QualifyingDetail>
     */
    #[ORM\OneToMany(targetEntity: QualifyingDetail::class, mappedBy: 'qualifyingCriteria')]
    private Collection $qualifyingDetails;

    #[ORM\Column(nullable: true)]
    private ?int $priority = null;

    public function __construct()
    {
        $this->qualifyings = new ArrayCollection();
        $this->qualifyingDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getMaxPoints(): ?int
    {
        return $this->maxPoints;
    }

    public function setMaxPoints(int $maxPoints): static
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    public function isBonus(): ?bool
    {
        return $this->isBonus;
    }

    public function setIsBonus(bool $isBonus): static
    {
        $this->isBonus = $isBonus;

        return $this;
    }

    /**
     * @return Collection<int, Qualifying>
     */
    public function getQualifyings(): Collection
    {
        return $this->qualifyings;
    }

    public function addQualifying(Qualifying $qualifying): static
    {
        if (!$this->qualifyings->contains($qualifying)) {
            $this->qualifyings->add($qualifying);
            $qualifying->setQualifyingCriteria($this);
        }

        return $this;
    }

    public function removeQualifying(Qualifying $qualifying): static
    {
        if ($this->qualifyings->removeElement($qualifying)) {
            // set the owning side to null (unless already changed)
            if ($qualifying->getQualifyingCriteria() === $this) {
                $qualifying->setQualifyingCriteria(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QualifyingDetail>
     */
    public function getQualifyingDetails(): Collection
    {
        return $this->qualifyingDetails;
    }

    public function addQualifyingDetail(QualifyingDetail $qualifyingDetail): static
    {
        if (!$this->qualifyingDetails->contains($qualifyingDetail)) {
            $this->qualifyingDetails->add($qualifyingDetail);
            $qualifyingDetail->setQualifyingCriteria($this);
        }

        return $this;
    }

    public function removeQualifyingDetail(QualifyingDetail $qualifyingDetail): static
    {
        if ($this->qualifyingDetails->removeElement($qualifyingDetail)) {
            // set the owning side to null (unless already changed)
            if ($qualifyingDetail->getQualifyingCriteria() === $this) {
                $qualifyingDetail->setQualifyingCriteria(null);
            }
        }

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}
