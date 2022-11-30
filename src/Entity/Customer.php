<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use App\Controller\Back\Stats\NbNewCustomerAction;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ApiResource(
    
    attributes: [
        "security" => "is_granted('ROLE_STATS')",
        "security_message" => "Accès refusé",
    ],
    collectionOperations: [
        'get',
        // NB NEW CLIENT 
        'getNbNewCustomer' => [
            'method' => 'GET',
            'path' => 'stats/nbNewCustomer',
            'controller' => NbNewCustomerAction::class,
            'read' => false,
            'pagination_enabled' => false,
            'openapi_context' => [
                'summary' => 'Recupère le nombre total de nouveaux clients',
                'parameters' => [],
                'responses' => [
                    '200' => [
                        'description' => 'Nombre de nouveaux clients',
                        'content' => [
                            'application/json' => [
                                'schema'=> [
                                    'type' => 'integer',
                                    'example' => 200
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ],
    ],
    itemOperations: [
        'get'
    ],
)]
#[UniqueEntity(fields: ['username'], message: 'Un compte utilise déja ce nom d\'utilisateur')]
#[UniqueEntity(fields: ['email'], message: 'Un compte utilise déja cette adresse email')]

class Customer implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 180, unique: true)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 2,
            'max' => 180,
            'minMessage' => 'Veuiller entrer un username contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un username contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $username = null;

    #[ORM\Column(length: 180, unique: true)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Email([
            'message' => 'Cette adresse mail n\'est pas valide.',
        ])
    ]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];


    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[
        Assert\Regex([
            'pattern' => '/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{14,}$/',
            'message' => 'Votre mot de passe doit contenir au minimum 14 caractères avec une 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial ',
        ])
    ]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 3,
            'max' => 255,
            'minMessage' => 'Veuiller entrer un prenom contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un prenom contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
        Assert\Length([
            'min' => 3,
            'max' => 255,
            'minMessage' => 'Veuiller entrer un nom contenant au minimum {{ limit }} caractères',
            'maxMessage' => 'Veuiller entrer un nom contenant au maximum {{ limit }} caractères',
        ]),
    ]
    private ?string $lastName = null;

    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
  
    ]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[
        Assert\NotBlank([
            'message' => "Veuiller remplir tout les champs."
        ]),
    ]
    private ?\DateTimeInterface $registrationDate = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Review::class)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Basket::class)]
    private Collection $baskets;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gender $gender = null;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    private ?Address $address = null;


    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->baskets = new ArrayCollection();
        $this->registrationDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setCustomer($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getCustomer() === $this) {
                $review->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Basket>
     */
    public function getBaskets(): Collection
    {
        return $this->baskets;
    }

    public function addBasket(Basket $basket): self
    {
        if (!$this->baskets->contains($basket)) {
            $this->baskets->add($basket);
            $basket->setCustomer($this);
        }

        return $this;
    }

    public function removeBasket(Basket $basket): self
    {
        if ($this->baskets->removeElement($basket)) {
            // set the owning side to null (unless already changed)
            if ($basket->getCustomer() === $this) {
                $basket->setCustomer(null);
            }
        }

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAddress(): ?address
    {
        return $this->address;
    }

    public function setAddress(?address $address): self
    {
        $this->address = $address;

        return $this;
    }


  
}

