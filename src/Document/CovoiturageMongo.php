<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;


#[ODM\Document(collection: 'covoiturages')]
#[ODM\HasLifecycleCallbacks()]
class CovoiturageMongo
{
    #[ODM\Id]
    /* #[ODM\GeneratedValue]
    #[ODM\Column] */
    private ?string $id = null;

    #[Assert\NotBlank (message:"Le tarif est obligatoire.")]
    #[ODM\Field(type: "int")]
    /* #[ODM\Column] */
    private ?int $prix = null;

    #[Assert\NotBlank (message:"Veuillez renseigner la date de départ.")]
    #[ODM\Field(type: "date")]
    /* #[ODM\Column(type: Types::DATETIME_MUTABLE)] */
    private ?\DateTimeInterface $dateDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu de départ.")]
    #[ODM\Field(type: "string")]
    /* #[ODM\Column(length: 255)] */
    private ?string $lieuDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure de départ.")]
    #[ODM\Field(type: "date")]
    /* #[ODM\Column(type: Types::DATETIME_MUTABLE)] */
    private ?\DateTimeInterface $heureDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu d'arrivée.")]
    #[ODM\Field(type: "string")]
   /*  #[ODM\Column(length: 255)] */
    private ?string $lieuArrivee = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure d'arrivée.")]
    #[ODM\Field(type: "date")]
    /* #[ODM\Column(type: Types::DATETIME_MUTABLE)] */
    private ?\DateTimeInterface $heureArrivee = null;

    /* #[ODM\Column()] */
    #[ODM\Field(type: "bool")]
    private ?bool $status = true;

    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'integer')]
    #[ODM\Field(type: "int")]
    /* #[ODM\Column(nullable: true)] */
    private ?int $placeDispo = null;

    #[ODM\Field(type: "string")] // Stocke l'ID de la voiture (relation avec MySQL)
    private ?string $voitureId = null;

    #[ODM\Field(type: "string")] // Stocke l'ID du conducteur (relation avec MySQL)
    private ?string $conducteurId = null;

    /* #[ODM\Column] */
    #[ODM\Field(type: "date")]
    private ?\DateTimeImmutable $createdAt = null;

   /*  #[ODM\Column] */
    #[ODM\Field(type: "bool")]
    private ?bool $isGo = false;

    /* #[ODM\Column] */
    #[ODM\Field(type: "bool")]
    private ?bool $isArrived = false;

    #[ODM\Field(type: "collection")] // Stocke une liste d'IDs d'utilisateurs
    private array $passagersIds = [];
    
    private ?bool $dateFuture = null;
    private ?bool $dateAujourdhui = null;

    /* /**
     * @var Collection<int, Utilisateur>
    
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, mappedBy: 'covoiturage')]
    private Collection $utilisateurs;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "conducteur_id", referencedColumnName: "id")]
    private ?Utilisateur $conducteur = null;


    /**
     * @var Collection<int, Utilisateur>
     
    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'validateCovoiturages')]
    private Collection $validateUsers;

    /**
     * @var Collection<int, Avis>
     
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'covoiturage')]
    private Collection $avis;


    public function __construct()
    {
        $this->utilisateurs = new ArrayCollection();
        $this->validateUsers = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }
 */
    public function getId(): ?string
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

    
    public function getUtilisateurs(): array
    {
        return $this->passagersIds;
    }

    public function addUtilisateur(string $passagerId): static
    {
      if (!in_array($passagerId, $this->passagersIds,true)) {
        $this->passagersIds[] = $passagerId;
    }

        return $this;
    }

    public function removeUtilisateur(string $passagerId): static
    {
        $index = array_search($passagerId, $this->passagersIds, true);
        if ($index !== false) {
            unset($this->passagersIds[$index]); // Supprime l'ID du passager
            $this->passagersIds = array_values($this->passagersIds); // Réindexe le tableau
        }
    
        return $this;
    }
    

    public function getVoiture(): ?string
    {
        return $this->voitureId;
    }

    public function setVoiture(string $voitureId): static
    {
        $this->voitureId = $voitureId;

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
    #[ODM\PrePersist]
    public function setCreatedAtValue()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getConducteur(): ?string
    {
        return $this->conducteurId;
    }

    public function setConducteur(string $conducteurId): static
    {
        $this->conducteurId = $conducteurId;

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
/* 
    /**
     * @return Collection<int, utilisateur>
     
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
    } */

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
/* 
    /**
     * @return Collection<int, Avis>
     
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
    } */
    
}
