<?php

namespace App\Entity\Order;

use App\Entity\User\Address;
use App\Repository\Order\ShipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipmentRepository::class)]
class Shipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $deliveryAddressId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $billingAddressId = null;

    #[ORM\Column(length: 17)]
    private ?string $orderNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $trackingNumber = null;

    #[ORM\ManyToOne(inversedBy: 'shipments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $orderId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrderStatus $statusId = null;

    #[ORM\ManyToOne]
    private ?Carrier $carrierId = null;

    /**
     * @var Collection<int, ShipmentItem>
     */
    #[ORM\OneToMany(targetEntity: ShipmentItem::class, mappedBy: 'shipmentId')]
    private Collection $shipmentItems;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->shipmentItems = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeliveryAddressId(): ?Address
    {
        return $this->deliveryAddressId;
    }

    public function setDeliveryAddressId(?Address $deliveryAddressId): static
    {
        $this->deliveryAddressId = $deliveryAddressId;

        return $this;
    }

    public function getBillingAddressId(): ?Address
    {
        return $this->billingAddressId;
    }

    public function setBillingAddressId(?Address $billingAddressId): static
    {
        $this->billingAddressId = $billingAddressId;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(?string $trackingNumber): static
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(?Order $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getStatusId(): ?OrderStatus
    {
        return $this->statusId;
    }

    public function setStatusId(?OrderStatus $statusId): static
    {
        $this->statusId = $statusId;

        return $this;
    }

    public function getCarrierId(): ?Carrier
    {
        return $this->carrierId;
    }

    public function setCarrierId(?Carrier $carrierId): static
    {
        $this->carrierId = $carrierId;

        return $this;
    }

    /**
     * @return Collection<int, ShipmentItem>
     */
    public function getShipmentItems(): Collection
    {
        return $this->shipmentItems;
    }

    public function addShipmentItem(ShipmentItem $shipmentItem): static
    {
        if (!$this->shipmentItems->contains($shipmentItem)) {
            $this->shipmentItems->add($shipmentItem);
            $shipmentItem->setShipmentId($this);
        }

        return $this;
    }

    public function removeShipmentItem(ShipmentItem $shipmentItem): static
    {
        if ($this->shipmentItems->removeElement($shipmentItem)) {
            // set the owning side to null (unless already changed)
            if ($shipmentItem->getShipmentId() === $this) {
                $shipmentItem->setShipmentId(null);
            }
        }

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
