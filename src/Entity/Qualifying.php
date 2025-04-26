<?php

namespace App\Entity;

use App\Repository\QualifyingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?int $passage = null;

    #[ORM\Column]
    #[Groups(['qualifying'])]
    private ?bool $isValid = null;

    /**
     * @var Collection<int, QualifyingDetail>
     */
    #[ORM\OneToMany(targetEntity: QualifyingDetail::class, mappedBy: 'qualifying')]
    #[Groups(['qualifyingDetails'])]
    private Collection $qualifyingDetails;

    public function __construct()
    {
        $this->qualifyingDetails = new ArrayCollection();
    }

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

    public function getPassage(): ?int
    {
        return $this->passage;
    }

    public function setPassage(int $passage): static
    {
        $this->passage = $passage;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): static
    {
        $this->isValid = $isValid;

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
            $qualifyingDetail->setQualifying($this);
        }

        return $this;
    }

    public function removeQualifyingDetail(QualifyingDetail $qualifyingDetail): static
    {
        if ($this->qualifyingDetails->removeElement($qualifyingDetail)) {
            // set the owning side to null (unless already changed)
            if ($qualifyingDetail->getQualifying() === $this) {
                $qualifyingDetail->setQualifying(null);
            }
        }

        return $this;
    }
}
