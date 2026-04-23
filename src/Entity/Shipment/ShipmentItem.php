<?php

namespace App\Entity\Shipment;

use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;
use App\Entity\Order\OrderProduct;
use App\Repository\Shipment\ShipmentItemRepository;

#[ORM\Entity(repositoryClass: ShipmentItemRepository::class)]
class ShipmentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'shipmentItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shipment $shipmentId = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?OrderProduct $orderProductId = null;

    use TimestampableTrait;

    public function __construct()
    {
        $this->setCreatedAtValue();
        $this->setUpdatedAtValue();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShipmentId(): ?Shipment
    {
        return $this->shipmentId;
    }

    public function setShipmentId(?Shipment $shipmentId): static
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    public function getOrderProductId(): ?OrderProduct
    {
        return $this->orderProductId;
    }

    public function setOrderProductId(?OrderProduct $orderProductId): static
    {
        $this->orderProductId = $orderProductId;

        return $this;
    }
}
