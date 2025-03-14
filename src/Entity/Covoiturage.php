<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
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

    #[Assert\NotBlank (message:"Le tarif est obligatoire.")]
    #[ORM\Column]
    private ?int $prix = null;

    #[Assert\NotBlank (message:"Veuillez renseigner la date de départ.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu de départ.")]
    #[ORM\Column(length: 255)]
    private ?string $lieuDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure de départ.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu d'arrivée.")]
    #[ORM\Column(length: 255)]
    private ?string $lieuArrivee = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure d'arrivée.")]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $heureArrivee = null;

    #[ORM\Column()]
    private ?bool $status = true;

    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'integer')]
    #[ORM\Column(nullable: true)]
    private ?int $placeDispo = null;

    #[ORM\ManyToOne(inversedBy: 'covoiturages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Voiture $voiture = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $isGo = false;

    #[ORM\Column]
    private ?bool $isArrived = false;
    
    private ?bool $dateFuture = null;
    private ?bool $dateAujourdhui = null;

    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'covoiturage')]
    private Collection $utilisateurs;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "conducteur_id", referencedColumnName: "id")]
    private ?Utilisateur $conducteur = null;


    /**
     * @var Collection<int, Utilisateur>
     */
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'validateCovoiturages')]
    private Collection $validateUsers;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'covoiturage')]
    private Collection $avis;


    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->validateUsers = new ArrayCollection();
        $this->avis = new ArrayCollection();
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

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvi(Avis $avi): static
    {
        if (!$this->avis->contains($avi)) {
            $this->avis->add($avi);
            $avi->setCovoiturage($this);
        }

        return $this;
    }

    public function removeAvis(Avis $avi): static
    {
        if ($this->avis->removeElement($avi)) {
            // set the owning side to null (unless already changed)
            if ($avi->getCovoiturage() === $this) {
                $avi->setCovoiturage(null);
            }
        }

        return $this;
    }
    
}
