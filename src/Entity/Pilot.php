<?php

namespace App\Entity;

use App\Repository\PilotRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PilotRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Pilot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pilot'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pilot'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['pilot'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $address = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $city = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $nationality = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['pilot'])]
    private ?bool $ffsaLicensee = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pilot'])]
    private ?string $ffsaNumber = null;

    /**
     * @var Collection<int, PilotRoundCategory>
     */
    #[ORM\OneToMany(targetEntity: PilotRoundCategory::class, mappedBy: 'pilot')]
    private Collection $pilotRoundCategories;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['pilot'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, PilotEvent>
     */
    #[ORM\OneToMany(targetEntity: PilotEvent::class, mappedBy: 'pilot', orphanRemoval: true)]
    #[Groups(['pilotEvents'])]
    private Collection $pilotEvents;

    public function __construct()
    {
        $this->pilotRoundCategories = new ArrayCollection();
        $this->pilotEvents = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updatedTimestamps(): void
    {
        $now = new DateTime();
        $this->setUpdatedAt($now);
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt($now);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function isFfsaLicensee(): ?bool
    {
        return $this->ffsaLicensee;
    }

    public function setFfsaLicensee(?bool $ffsaLicensee): static
    {
        $this->ffsaLicensee = $ffsaLicensee;

        return $this;
    }

    public function getFfsaNumber(): ?string
    {
        return $this->ffsaNumber;
    }

    public function setFfsaNumber(?string $ffsaNumber): static
    {
        $this->ffsaNumber = $ffsaNumber;

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
            $pilotRoundCategory->setPilot($this);
        }

        return $this;
    }

    public function removePilotRoundCategory(PilotRoundCategory $pilotRoundCategory): static
    {
        if ($this->pilotRoundCategories->removeElement($pilotRoundCategory)) {
            // set the owning side to null (unless already changed)
            if ($pilotRoundCategory->getPilot() === $this) {
                $pilotRoundCategory->setPilot(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
            $pilotEvent->setPilot($this);
        }

        return $this;
    }

    public function removePilotEvent(PilotEvent $pilotEvent): static
    {
        if ($this->pilotEvents->removeElement($pilotEvent)) {
            // set the owning side to null (unless already changed)
            if ($pilotEvent->getPilot() === $this) {
                $pilotEvent->setPilot(null);
            }
        }

        return $this;
    }
}
