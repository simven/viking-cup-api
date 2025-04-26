<?php

namespace App\Entity;

use App\Repository\PenaltyReasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PenaltyReasonRepository::class)]
class PenaltyReason
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['penaltyReason'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['penaltyReason'])]
    private ?string $name = null;

    /**
     * @var Collection<int, Penalty>
     */
    #[ORM\OneToMany(targetEntity: Penalty::class, mappedBy: 'penaltyReason')]
    private Collection $penalties;

    #[ORM\Column(nullable: true)]
    private ?int $suggestedPoints = null;

    public function __construct()
    {
        $this->penalties = new ArrayCollection();
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

    /**
     * @return Collection<int, Penalty>
     */
    public function getPenalties(): Collection
    {
        return $this->penalties;
    }

    public function addPenalty(Penalty $penalty): static
    {
        if (!$this->penalties->contains($penalty)) {
            $this->penalties->add($penalty);
            $penalty->setPenaltyReason($this);
        }

        return $this;
    }

    public function removePenalty(Penalty $penalty): static
    {
        if ($this->penalties->removeElement($penalty)) {
            // set the owning side to null (unless already changed)
            if ($penalty->getPenaltyReason() === $this) {
                $penalty->setPenaltyReason(null);
            }
        }

        return $this;
    }

    public function getSuggestedPoints(): ?int
    {
        return $this->suggestedPoints;
    }

    public function setSuggestedPoints(?int $suggestedPoints): static
    {
        $this->suggestedPoints = $suggestedPoints;

        return $this;
    }
}
