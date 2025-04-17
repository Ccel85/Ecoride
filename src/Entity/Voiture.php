<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\VoitureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoitureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Voiture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"L'\immatriculation est obligatoire.")]
    #[Assert\Regex('/[a-zA-Z][a-zA-Z]-[0-9][0-9][0-9]-[a-zA-Z][a-zA-Z]/i')]
    #[ORM\Column(length: 255)]
    private ?string $immat = null;

    #[Assert\NotBlank(message:"La date de première immatriculation est obligatoire.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $firstImmat = null;

    #[Assert\NotBlank(message:"Le constructeur est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $constructeur = null;

    #[Assert\NotBlank (message:"Le modèle est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $modele = null;

    #[Assert\NotBlank (message:"La couleur est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $couleur = null;

    #[Assert\NotBlank (message:"L'\énergie est obligatoire.")]
    #[ORM\Column(length: 255)]
    private ?string $energie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $options = null;

    #[Assert\NotBlank (message:"Le nombre de place est obligatoire.")]
    #[ORM\Column]
    private ?int $nbrePlace = null;

    #[ORM\ManyToOne(inversedBy: 'voiture')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;  
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImmat(): ?string
    {
        return $this->immat;
    }

    public function setImmat(string $immat): static
    {
        $this->immat = $immat;

        return $this;
    }

    public function getFirstImmat(): ?\DateTimeInterface
    {
        return $this->firstImmat;
    }

    public function setFirstImmat(\DateTimeInterface $firstImmat): static
    {
        $this->firstImmat = $firstImmat;

        return $this;
    }

    public function getConstructeur(): ?string
    {
        return $this->constructeur;
    }

    public function setConstructeur(string $constructeur): static
    {
        $this->constructeur = $constructeur;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getEnergie(): ?string
    {
        return $this->energie;
    }

    public function setEnergie(string $energie): static
    {
        $this->energie = $energie;

        return $this;
    }

    public function getOptions(): ?string
    {
        return $this->options;
    }

    public function setOptions(?string $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function getNbrePlace(): ?int
    {
        return $this->nbrePlace;
    }

    public function setNbrePlace(int $nbrePlace): static
    {
        $this->nbrePlace = $nbrePlace;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }
}
