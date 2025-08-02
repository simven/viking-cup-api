<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['event'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['event'])]
    private ?int $year = null;

    /**
     * @var Collection<int, Round>
     */
    #[ORM\OneToMany(targetEntity: Round::class, mappedBy: 'event')]
    #[Groups(['eventRounds'])]
    private Collection $rounds;

    /**
     * @var Collection<int, PilotEvent>
     */
    #[ORM\OneToMany(targetEntity: PilotEvent::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $pilotEvents;

    /**
     * @var Collection<int, PilotNumberCounter>
     */
    #[ORM\OneToMany(targetEntity: PilotNumberCounter::class, mappedBy: 'event')]
    private Collection $pilotNumberCounters;

    public function __construct()
    {
        $this->rounds = new ArrayCollection();
        $this->pilotEvents = new ArrayCollection();
        $this->pilotNumberCounters = new ArrayCollection();
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

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return Collection<int, Round>
     */
    public function getRounds(): Collection
    {
        return $this->rounds;
    }

    public function addRound(Round $round): static
    {
        if (!$this->rounds->contains($round)) {
            $this->rounds->add($round);
            $round->setEvent($this);
        }

        return $this;
    }

    public function removeRound(Round $round): static
    {
        if ($this->rounds->removeElement($round)) {
            // set the owning side to null (unless already changed)
            if ($round->getEvent() === $this) {
                $round->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PilotEvent>
     */
    public function getPilotEvents(): Collection
    {
        return $this->pilotEvents;
    }

    public function addPilotEvent(PilotEvent $pilotEvent): static
    {
        if (!$this->pilotEvents->contains($pilotEvent)) {
            $this->pilotEvents->add($pilotEvent);
            $pilotEvent->setEvent($this);
        }

        return $this;
    }

    public function removePilotEvent(PilotEvent $pilotEvent): static
    {
        if ($this->pilotEvents->removeElement($pilotEvent)) {
            // set the owning side to null (unless already changed)
            if ($pilotEvent->getEvent() === $this) {
                $pilotEvent->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PilotNumberCounter>
     */
    public function getPilotNumberCounters(): Collection
    {
        return $this->pilotNumberCounters;
    }

    public function addPilotNumberCounter(PilotNumberCounter $pilotNumberCounter): static
    {
        if (!$this->pilotNumberCounters->contains($pilotNumberCounter)) {
            $this->pilotNumberCounters->add($pilotNumberCounter);
            $pilotNumberCounter->setEvent($this);
        }

        return $this;
    }

    public function removePilotNumberCounter(PilotNumberCounter $pilotNumberCounter): static
    {
        if ($this->pilotNumberCounters->removeElement($pilotNumberCounter)) {
            // set the owning side to null (unless already changed)
            if ($pilotNumberCounter->getEvent() === $this) {
                $pilotNumberCounter->setEvent(null);
            }
        }

        return $this;
    }
}
