<?php

namespace App\Entity;

use App\Repository\RoundRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: RoundRepository::class)]
class Round
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['round'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['round'])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'rounds')]
    #[Groups(['roundEvent'])]
    private ?Event $event = null;

    /**
     * @var Collection<int, RoundDetail>
     */
    #[ORM\OneToMany(targetEntity: RoundDetail::class, mappedBy: 'round', orphanRemoval: true)]
    #[Groups(['roundDetails'])]
    private Collection $roundDetails;

    /**
     * @var Collection<int, PilotRoundCategory>
     */
    #[ORM\OneToMany(targetEntity: PilotRoundCategory::class, mappedBy: 'round')]
    private Collection $pilotRoundCategories;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['round'])]
    private ?\DateTimeInterface $fromDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['round'])]
    private ?\DateTimeInterface $toDate = null;

    /**
     * @var Collection<int, RoundCategory>
     */
    #[ORM\OneToMany(targetEntity: RoundCategory::class, mappedBy: 'round')]
    private Collection $roundCategories;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, mappedBy: 'rounds')]
    private Collection $people;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'round')]
    private Collection $medias;

    public function __construct()
    {
        $this->roundDetails = new ArrayCollection();
        $this->pilotRoundCategories = new ArrayCollection();
        $this->roundCategories = new ArrayCollection();
        $this->people = new ArrayCollection();
        $this->medias = new ArrayCollection();
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection<int, RoundDetail>
     */
    public function getRoundDetails(): Collection
    {
        return $this->roundDetails;
    }

    public function addRoundDetail(RoundDetail $roundDetail): static
    {
        if (!$this->roundDetails->contains($roundDetail)) {
            $this->roundDetails->add($roundDetail);
            $roundDetail->setRound($this);
        }

        return $this;
    }

    public function removeRoundDetail(RoundDetail $roundDetail): static
    {
        if ($this->roundDetails->removeElement($roundDetail)) {
            // set the owning side to null (unless already changed)
            if ($roundDetail->getRound() === $this) {
                $roundDetail->setRound(null);
            }
        }

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
            $pilotRoundCategory->setRound($this);
        }

        return $this;
    }

    public function removePilotRoundCategory(PilotRoundCategory $pilotRoundCategory): static
    {
        if ($this->pilotRoundCategories->removeElement($pilotRoundCategory)) {
            // set the owning side to null (unless already changed)
            if ($pilotRoundCategory->getRound() === $this) {
                $pilotRoundCategory->setRound(null);
            }
        }

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(?\DateTimeInterface $fromDate): static
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(?\DateTimeInterface $toDate): static
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * @return Collection<int, RoundCategory>
     */
    public function getRoundCategories(): Collection
    {
        return $this->roundCategories;
    }

    public function addRoundCategory(RoundCategory $roundCategory): static
    {
        if (!$this->roundCategories->contains($roundCategory)) {
            $this->roundCategories->add($roundCategory);
            $roundCategory->setRound($this);
        }

        return $this;
    }

    public function removeRoundCategory(RoundCategory $roundCategory): static
    {
        if ($this->roundCategories->removeElement($roundCategory)) {
            // set the owning side to null (unless already changed)
            if ($roundCategory->getRound() === $this) {
                $roundCategory->setRound(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->addRound($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            $person->removeRound($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Media>
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media): static
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
            $media->setRound($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getRound() === $this) {
                $media->setRound(null);
            }
        }

        return $this;
    }
}
