<?php

namespace App\Entity;

use App\Repository\RoundCategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RoundCategoryRepository::class)]
class RoundCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['roundCategory'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'roundCategories')]
    #[Groups(['roundCategoryRound'])]
    private ?Round $round = null;

    #[ORM\ManyToOne(inversedBy: 'roundCategories')]
    #[Groups(['roundCategoryCategory'])]
    private ?Category $category = null;

    #[ORM\Column]
    #[Groups(['roundCategory'])]
    private ?bool $displayTop = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isDisplayTop(): ?bool
    {
        return $this->displayTop;
    }

    public function setDisplayTop(bool $displayTop): static
    {
        $this->displayTop = $displayTop;

        return $this;
    }
}
