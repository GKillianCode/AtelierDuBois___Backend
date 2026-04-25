<?php

namespace App\Entity\User;

use App\Enum\UserType;
use App\Entity\Order\Order;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\User\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Validator\Constraints\Password\StrongPassword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_UUID', fields: ['uuid'])]
#[UniqueEntity(fields: ['email'], message: 'user.email.already_used')]
#[UniqueEntity(fields: ['uuid'], message: 'user.uuid.already_used')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'user.uuid.not_blank', groups: ['registration'])]
    #[Assert\Uuid(message: 'user.uuid.invalid', groups: ['registration'])]
    private ?string $uuid = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\Type(type: 'array', message: 'user.roles.type', groups: ['registration'])]
    #[Assert\All([
        new Assert\Type(type: 'string', message: 'user.roles.item_type', groups: ['registration']),
        new Assert\Regex(pattern: '/^ROLE_[A-Z_]+$/', message: 'user.roles.item_format', groups: ['registration'])
    ])]
    private array $roles = [];

    #[ORM\Column(length: 20, enumType: UserType::class)]
    private UserType $userType;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'user.password.not_blank', groups: ['password_change'])]
    private ?string $password = null;

    #[StrongPassword(groups: ['registration'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'user.email.not_blank', groups: ['registration'])]
    #[Assert\Email(message: 'user.email.invalid', groups: ['registration'])]
    #[Assert\Length(
        max: 150,
        maxMessage: 'user.email.max_length'
    )]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'user.firstname.not_blank', groups: ['registration'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'user.firstname.min_length',
        maxMessage: 'user.firstname.max_length',
        groups: ['registration']
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
        message: 'user.firstname.regex',
        groups: ['registration']
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'user.lastname.not_blank', groups: ['registration'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'user.lastname.min_length',
        maxMessage: 'user.lastname.max_length',
        groups: ['registration']
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ\-\s]+$/',
        message: 'user.lastname.regex',
        groups: ['registration']
    )]
    private ?string $lastname = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'user.created_at.not_null', groups: ['registration'])]
    #[Assert\Type(type: '\DateTimeImmutable', message: 'user.created_at.type', groups: ['registration'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'user.updated_at.not_null', groups: ['registration'])]
    #[Assert\Type(type: '\DateTimeImmutable', message: 'user.updated_at.type', groups: ['registration'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Address>
     */
    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'userId')]
    private Collection $addresses;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'userId')]
    private Collection $orders;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->addresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
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

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    public function setUserType(UserType $userType): static
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    // Méthode pour effacer le plainPassword après encodage
    public function erasePlainPassword(): void
    {
        $this->plainPassword = null;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

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

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): static
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUserId($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): static
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getUserId() === $this) {
                $address->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setUserId($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUserId() === $this) {
                $order->setUserId(null);
            }
        }

        return $this;
    }
}
