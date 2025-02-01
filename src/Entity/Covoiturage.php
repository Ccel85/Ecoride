<?php

namespace App\Entity;

use App\Repository\CovoiturageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CovoiturageRepository::class)]
#[ORM\HasLifecycleCallbacks()]
class Covoiturage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepart = null;

    #[ORM\Column(length: 255)]
    private ?string $lieuDepart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureDepart = null;

    #[ORM\Column(length: 255)]
    private ?string $lieuArrivee = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureArrivee = null;

    #[ORM\Column()]
    private ?bool $status = true;

    #[ORM\Column(nullable: true)]
    private ?int $placeDispo = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'covoiturage')]
    private Collection $utilisateurs;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?voiture $voiture = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "conducteur_id", referencedColumnName: "id")]
    private ?Utilisateur $conducteur = null;

    #[ORM\Column]
    private ?bool $isGo = false;

    #[ORM\Column]
    private ?bool $isArrived = false;

    /**
     * @var Collection<int, utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'validateCovoiturages')]
    private Collection $validateUsers;

    private ?bool $dateFuture = null;
    private ?bool $dateAujourdhui = null;

    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->validateUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getDateDepart(): ?\DateTimeInterface
    {
        return $this->dateDepart;
    }

    public function setDateDepart(\DateTimeInterface $dateDepart): static
    {
        $this->dateDepart = $dateDepart;

        return $this;
    }

    public function getLieuDepart(): ?string
    {
        return $this->lieuDepart;
    }

    public function setLieuDepart(string $lieuDepart): static
    {
        $this->lieuDepart = $lieuDepart;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heureDepart;
    }

    public function setHeureDepart(\DateTimeInterface $heureDepart): static
    {
        $this->heureDepart = $heureDepart;

        return $this;
    }

    public function getLieuArrivee(): ?string
    {
        return $this->lieuArrivee;
    }

    public function setLieuArrivee(string $lieuArrivee): static
    {
        $this->lieuArrivee = $lieuArrivee;

        return $this;
    }

    public function getHeureArrivee(): ?\DateTimeInterface
    {
        return $this->heureArrivee;
    }

    public function setHeureArrivee(\DateTimeInterface $heureArrivee): static
    {
        $this->heureArrivee = $heureArrivee;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPlaceDispo(): ?int
    {
        return $this->placeDispo;
    }

    public function setPlaceDispo(?int $placeDispo): static
    {
        $this->placeDispo = $placeDispo;

        return $this;
    }

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): static
    {
        if (!$this->utilisateurs->contains($utilisateur)) {
            $this->utilisateurs->add($utilisateur);
            $utilisateur->addCovoiturage($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): static
    {
        if ($this->utilisateurs->removeElement($utilisateur)) {
            $utilisateur->removeCovoiturage($this);
        }

        return $this;
    }

    public function getVoiture(): ?voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?voiture $voiture): static
    {
        $this->voiture = $voiture;

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
    #[ORM\PrePersist]
    public function setCreatedAtValue()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getConducteur(): ?Utilisateur
    {
        return $this->conducteur;
    }

    public function setConducteur(?Utilisateur $conducteur): self
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    public function isGo(): ?bool
    {
        return $this->isGo;
    }

    public function setGo(bool $isGo): static
    {
        $this->isGo = $isGo;

        return $this;
    }

    public function isArrived(): ?bool
    {
        return $this->isArrived;
    }

    public function setArrived(bool $isArrived): static
    {
        $this->isArrived = $isArrived;

        return $this;
    }

    /**
     * @return Collection<int, utilisateur>
     */
    public function getValidateUsers(): Collection
    {
        return $this->validateUsers;
    }

    public function addValidateUser(utilisateur $validateUser): static
    {
        if (!$this->validateUsers->contains($validateUser)) {
            $this->validateUsers->add($validateUser);
        }

        return $this;
    }

    public function removeValidateUser(utilisateur $validateUser): static
    {
        $this->validateUsers->removeElement($validateUser);

        return $this;
    }

    public function isDateFuture(): ?bool
    {
        return $this->dateFuture;
    }

    public function setDateFuture(?bool $dateFuture): self
    {
        $this->dateFuture = $dateFuture;

        return $this;
    }

    public function isDateAujourdhui(): ?bool
    {
        return $this->dateAujourdhui;
    }

    public function setDateAujourdhui(?bool $dateAujourdhui): self
    {
        $this->dateAujourdhui = $dateAujourdhui;

        return $this;
    }
    
}
