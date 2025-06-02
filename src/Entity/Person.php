<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('person')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    #[Groups('personPersonType')]
    private ?PersonType $personType = null;

    #[ORM\Column(length: 255)]
    #[Groups('person')]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups('person')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('person')]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('person')]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['person'])]
    private ?string $address = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['person'])]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['person'])]
    private ?string $city = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(['person'])]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['person'])]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('person')]
    private ?string $role = null;

    #[ORM\Column(nullable: true)]
    #[Groups('person')]
    private ?int $mark = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('person')]
    private ?string $comment = null;

    #[ORM\Column]
    #[Groups('person')]
    private ?int $warnings = null;

    /**
     * @var Collection<int, Link>
     */
    #[ORM\ManyToMany(targetEntity: Link::class, inversedBy: 'people')]
    private Collection $links;

    #[ORM\Column(length: 255)]
    private ?string $uniqueId = null;

    /**
     * @var Collection<int, Round>
     */
    #[ORM\ManyToMany(targetEntity: Round::class, inversedBy: 'people')]
    private Collection $rounds;

    /**
     * @var Collection<int, RoundDetail>
     */
    #[ORM\ManyToMany(targetEntity: RoundDetail::class, inversedBy: 'people')]
    private Collection $roundDetails;

    /**
     * @var Collection<int, Media>
     */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'person')]
    #[Groups(['personMedias'])]
    private Collection $medias;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->rounds = new ArrayCollection();
        $this->roundDetails = new ArrayCollection();
        $this->medias = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function updatedTimestamps(): void
    {
        if ($this->uniqueId === null) {
            $this->uniqueId = uniqid('', true);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonType(): ?PersonType
    {
        return $this->personType;
    }

    public function setPersonType(?PersonType $personType): static
    {
        $this->personType = $personType;

        return $this;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getMark(): ?int
    {
        return $this->mark;
    }

    public function setMark(?int $mark): static
    {
        $this->mark = $mark;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getWarnings(): ?int
    {
        return $this->warnings;
    }

    public function setWarnings(int $warnings): static
    {
        $this->warnings = $warnings;

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(Link $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
        }

        return $this;
    }

    public function removeLink(Link $link): static
    {
        $this->links->removeElement($link);

        return $this;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(string $uniqueId): static
    {
        $this->uniqueId = $uniqueId;

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
        }

        return $this;
    }

    public function removeRound(Round $round): static
    {
        $this->rounds->removeElement($round);

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
        }

        return $this;
    }

    public function removeRoundDetail(RoundDetail $roundDetail): static
    {
        $this->roundDetails->removeElement($roundDetail);

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
            $media->setPerson($this);
        }

        return $this;
    }

    public function removeMedia(Media $media): static
    {
        if ($this->medias->removeElement($media)) {
            // set the owning side to null (unless already changed)
            if ($media->getPerson() === $this) {
                $media->setPerson(null);
            }
        }

        return $this;
    }
}
