<?php

namespace App\Entity;

use App\Repository\PilotEventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PilotEventRepository::class)]
class PilotEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pilotEvent'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'pilotEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pilot $pilot = null;

    #[ORM\ManyToOne(inversedBy: 'pilotEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['pilotEvent'])]
    private ?string $pilotNumber = null;

    #[ORM\Column]
    #[Groups(['pilotEvent'])]
    private ?bool $receiveWindscreenBand = null;

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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getPilotNumber(): ?string
    {
        return $this->pilotNumber;
    }

    public function setPilotNumber(?string $pilotNumber): static
    {
        $this->pilotNumber = $pilotNumber;

        return $this;
    }

    public function isReceiveWindscreenBand(): ?bool
    {
        return $this->receiveWindscreenBand;
    }

    public function setReceiveWindscreenBand(bool $receiveWindscreenBand): static
    {
        $this->receiveWindscreenBand = $receiveWindscreenBand;

        return $this;
    }
}
