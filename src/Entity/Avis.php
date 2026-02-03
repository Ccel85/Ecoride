<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AvisRepository;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
#[ORM\HasLifecycleCallbacks]

class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column]
    private ?bool $isValid = false;
    
    #[Assert\NotBlank (message:"Veuillez ajouter votre avis.")]
    #[ORM\Column(length: 255)]
    private ?string $comments = null;
    
    #[ORM\Column(nullable: true)]
    #[Assert\Type(
        type: 'integer')]
    private ?int $rateComments = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'avisConducteur')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $conducteur = null; // Celui qui reçoit l'avis
    
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'avisPassager')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $passager = null; // Celui qui donne l'avis

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $covoiturage = null; 

    #[ORM\Column(nullable: false)]
    private ?bool $isSignal = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->isValid;
    }

    public function setValid(bool $isValid): static
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(string $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    public function getRateComments(): ?int
    {
        return $this->rateComments;
    }

    public function setRateComments(?int $rateComments): static
    {
        $this->rateComments = $rateComments;

        return $this;
    }

    public function getConducteur(): ?Utilisateur
    {
        return $this->conducteur;
    }

    public function setConducteur(?Utilisateur $conducteur): static
    {
        $this->conducteur = $conducteur;

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

    public function getPassager(): ?Utilisateur
    {
        return $this->passager;
    }

    public function setPassager(?Utilisateur $passager): static
    {
        $this->passager = $passager;

        return $this;
    }


    public function isSignal(): ?bool
    {
        return $this->isSignal;
    }

    public function setIsSignal(?bool $isSignal): static
    {
        $this->isSignal = $isSignal;

        return $this;
    }

    public function getCovoiturage()
    {
        return $this->covoiturage;
    }

    public function setCovoiturage($covoiturage)
    {
        $this->covoiturage = $covoiturage;

        return $this;
    }
}
