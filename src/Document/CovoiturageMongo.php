<?php

namespace App\Document;

use App\Repository\CovoiturageMongoRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;

#[ODM\Document(
    repositoryClass: CovoiturageMongoRepository::class,
    collection: 'covoiturages'
)]
#[ODM\HasLifecycleCallbacks()]
class CovoiturageMongo
{
    #[ODM\Id(strategy: "AUTO")]
    private ?string $id = null;

    #[Assert\NotBlank (message:"Le tarif est obligatoire.")]
    #[ODM\Field(type: "int")]
    private ?int $prix = null;

    #[Assert\NotBlank (message:"Veuillez renseigner la date de départ.")]
    #[ODM\Field(type: "date")]
    private ?\DateTimeInterface $dateDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu de départ.")]
    #[ODM\Field(type: "string")]
    private ?string $lieuDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure de départ.")]
    #[ODM\Field(type: "date")]
    private ?\DateTimeInterface $heureDepart = null;

    #[Assert\NotBlank (message:"Veuillez renseigner le lieu d'arrivée.")]
    #[ODM\Field(type: "string")]
    private ?string $lieuArrivee = null;

    #[Assert\NotBlank (message:"Veuillez renseigner l'heure d'arrivée.")]
    #[ODM\Field(type: "date")]
    private ?\DateTimeInterface $heureArrivee = null;

    #[ODM\Field(type: "bool")]
    private ?bool $status = true;

    #[Assert\NotBlank]
    #[Assert\Type(
        type: 'integer')]
    #[ODM\Field(type: "int")]
    private ?int $placeDispo = null;

    #[ODM\Field(type: "string")] // Stocke l'ID de la voiture (relation avec MySQL)
    private ?string $voitureId = null;

    #[ODM\Field(type: "string")] // Stocke l'ID du conducteur (relation avec MySQL)
    private ?string $conducteurId = null;

    #[ODM\Field(type: "date")]
    private ?\DateTime $createdAt = null;

    #[ODM\Field(type: "bool")]
    private ?bool $isGo = false;

    #[ODM\Field(type: "bool")]
    private ?bool $isArrived = false;

    #[ODM\Field(type: "collection")] // Stocke une liste d'IDs d'utilisateurs
    private array $passagersIds = [];
    
    #[ODM\Field(type: 'collection')]
    private array $validateUsers = [];
    
    private ?float $rate = null;
    private ?string $duree = null;
    private ?bool $dateFuture = null;
    private ?bool $dateAujourdhui = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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
        return ucfirst(strtolower($this->lieuDepart));
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
        return ucfirst(strtolower($this->lieuArrivee));
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
    
    public function getPassagersIds(): array
    {
        return $this->passagersIds;
    }

    public function addPassagersIds(string $passagerId): static
    {
        if (!in_array($passagerId, $this->passagersIds,true)) {
        $this->passagersIds[] = $passagerId;
    }

        return $this;
    }

    public function removePassagersIds(string $passagerId): static
    {
        $index = array_search($passagerId, $this->passagersIds, true);
        if ($index !== false) {
            unset($this->passagersIds[$index]); // Supprime l'ID du passager
            $this->passagersIds = array_values($this->passagersIds); // Réindexe le tableau
        }
        return $this;
    }
    

    public function getVoitureId(): ?string
    {
        return $this->voitureId;
    }

    public function setVoitureId(string $voitureId): static
    {
        $this->voitureId = $voitureId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    #[ODM\PrePersist]
    public function setCreatedAtValue()
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTime();
        }
    }

    public function getConducteurId(): ?string
    {
        return $this->conducteurId;
    }

    public function setConducteurId(string $conducteurId): static
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

    public function getValidateUsers(): array
    {
        return $this->validateUsers;
    }

    public function addValidateUser(int $userId): void
    {
        if (!in_array($userId, $this->validateUsers, true)) {
            $this->validateUsers[] = $userId;
        }

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

    public function getDuree(): ?string
    {
        return $this->duree;
    }

    public function setDuree(?string $duree): static
    {
        $this->duree = $duree;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(?float $rate): static
    {
        $this->rate = $rate;

        return $this;
    }
    

}
