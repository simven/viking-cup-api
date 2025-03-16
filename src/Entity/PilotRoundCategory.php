<?php

namespace App\Entity;

use App\Repository\PilotRoundCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PilotRoundCategoryRepository::class)]
class PilotRoundCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pilotRoundCategory'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pilotRoundCategories')]
    #[Groups(['pilotRoundCategoryPilot'])]
    private ?Pilot $pilot = null;

    #[ORM\ManyToOne(inversedBy: 'pilotRoundCategories')]
    #[Groups(['pilotRoundCategoryRound'])]
    private ?Round $round = null;

    #[ORM\ManyToOne(inversedBy: 'pilotRoundCategories')]
    #[Groups(['pilotRoundCategoryCategory'])]
    private ?Category $category = null;

    #[ORM\ManyToOne]
    #[Groups(['pilotRoundCategorySecondPilot'])]
    private ?Pilot $secondPilot = null;

    #[ORM\Column]
    private ?bool $mainPilot = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilotRoundCategory'])]
    private ?string $vehicle = null;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['pilotRoundCategory'])]
    private bool $isCompeting = true;

    /**
     * @var Collection<int, Qualifying>
     */
    #[ORM\OneToMany(targetEntity: Qualifying::class, mappedBy: 'pilotRoundCategory')]
    #[Groups(['pilotRoundCategoryQualifyings'])]
    private Collection $qualifyings;

    public function __construct()
    {
        $this->qualifyings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPilot(): ?Pilot
    {
        return $this->pilot;
    }

    public function setPilot(?Pilot $pilot): static
    {
        $this->pilot = $pilot;

        return $this;
    }

    public function getRound(): ?Round
    {
        return $this->round;
    }

    public function setRound(?Round $round): static
    {
        $this->round = $round;

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

    public function getSecondPilot(): ?Pilot
    {
        return $this->secondPilot;
    }

    public function setSecondPilot(?Pilot $secondPilot): static
    {
        $this->secondPilot = $secondPilot;

        return $this;
    }

    public function isMainPilot(): ?bool
    {
        return $this->mainPilot;
    }

    public function setMainPilot(bool $mainPilot): static
    {
        $this->mainPilot = $mainPilot;

        return $this;
    }

    public function getVehicle(): ?string
    {
        return $this->vehicle;
    }

    public function setVehicle(?string $vehicle): static
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function isCompeting(): bool
    {
        return $this->isCompeting;
    }

    public function setIsCompeting(bool $isCompeting): static
    {
        $this->isCompeting = $isCompeting;

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
            $qualifying->setPilotRoundCategory($this);
        }

        return $this;
    }

    public function removeQualifying(Qualifying $qualifying): static
    {
        if ($this->qualifyings->removeElement($qualifying)) {
            // set the owning side to null (unless already changed)
            if ($qualifying->getPilotRoundCategory() === $this) {
                $qualifying->setPilotRoundCategory(null);
            }
        }

        return $this;
    }
}
