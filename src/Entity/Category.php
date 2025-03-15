<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, PilotRoundCategory>
     */
    #[ORM\OneToMany(targetEntity: PilotRoundCategory::class, mappedBy: 'category')]
    private Collection $pilotRoundCategories;

    public function __construct()
    {
        $this->pilotRoundCategories = new ArrayCollection();
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
     * @return Collection<int, PilotRoundCategory>
     */
    public function getPilotRoundCategories(): Collection
    {
        return $this->pilotRoundCategories;
    }

    public function addPilotRoundCategory(PilotRoundCategory $pilotRoundCategory): static
    {
        if (!$this->pilotRoundCategories->contains($pilotRoundCategory)) {
            $this->pilotRoundCategories->add($pilotRoundCategory);
            $pilotRoundCategory->setCategory($this);
        }

        return $this;
    }

    public function removePilotRoundCategory(PilotRoundCategory $pilotRoundCategory): static
    {
        if ($this->pilotRoundCategories->removeElement($pilotRoundCategory)) {
            // set the owning side to null (unless already changed)
            if ($pilotRoundCategory->getCategory() === $this) {
                $pilotRoundCategory->setCategory(null);
            }
        }

        return $this;
    }
}
