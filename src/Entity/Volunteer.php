<?php

namespace App\Entity;

use App\Repository\VolunteerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: VolunteerRepository::class)]
class Volunteer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('volunteer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'volunteers')]
    #[Groups('volunteerPerson')]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'volunteers')]
    #[Groups('volunteerRound')]
    private ?Round $round = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('volunteer')]
    private ?string $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
