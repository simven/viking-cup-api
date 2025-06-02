<?php

namespace App\Entity;

use App\Repository\MediaFileRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Attribute\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaFileRepository::class)]
#[Vich\Uploadable]
class MediaFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['media:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: ["img", "video"], message: "Le type doit être 'img' ou 'video'.")]
    #[Groups(['media:read'])]
    private ?string $type = "img";

    #[ORM\Column(length: 255)]
    #[Groups(['media:read'])]
    private ?string $filePath = null;

    #[Vich\UploadableField(mapping: "media_files", fileNameProperty: "filePath")]
    #[Assert\NotNull(message: "Le fichier est obligatoire.")]
    #[Assert\File(
        mimeTypes: ["image/jpeg", "image/png", "image/webp", "video/mp4"],
        mimeTypesMessage: "Seuls les fichiers JPEG, PNG, WEBP et MP4 sont autorisés."
    )]
    private ?File $file = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read'])]
    private ?string $alt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read'])]
    private ?string $author = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['media:read'])]
    private ?string $about = null;

    #[ORM\Column]
    #[Groups(['media:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(File $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): static
    {
        $this->about = $about;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
