<?php

namespace App\Entity\Order;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;
use App\Repository\Order\OrderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userId = null;

    #[ORM\Column]
    private ?int $total_price = null;

    /**
     * @var Collection<int, Shipment>
     */
    #[ORM\OneToMany(targetEntity: Shipment::class, mappedBy: 'orderId')]
    private Collection $shipments;

    use TimestampableTrait;

    public function __construct()
    {
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
        $this->shipments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getTotalPrice(): ?int
    {
        return $this->total_price;
    }

    public function setTotalPrice(int $total_price): static
    {
        $this->total_price = $total_price;

        return $this;
    }

    /**
     * @return Collection<int, Shipment>
     */
    public function getShipments(): Collection
    {
        return $this->shipments;
    }

    public function addShipment(Shipment $shipment): static
    {
        if (!$this->shipments->contains($shipment)) {
            $this->shipments->add($shipment);
            $shipment->setOrderId($this);
        }

        return $this;
    }

    public function removeShipment(Shipment $shipment): static
    {
        if ($this->shipments->removeElement($shipment)) {
            // set the owning side to null (unless already changed)
            if ($shipment->getOrderId() === $this) {
                $shipment->setOrderId(null);
            }
        }

        return $this;
    }
}
