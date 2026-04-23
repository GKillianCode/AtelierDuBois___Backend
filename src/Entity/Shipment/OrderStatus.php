<?php

namespace App\Entity\Shipment;

use App\Enum\ShipmentStatusCode;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;
use App\Repository\Shipment\OrderStatusRepository;

#[ORM\Entity(repositoryClass: OrderStatusRepository::class)]
class OrderStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $code = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?ShipmentStatusCode
    {
        return ShipmentStatusCode::tryFrom($this->code);
    }

    public function setCode(ShipmentStatusCode $code): static
    {
        $this->code = $code->value;

        return $this;
    }
}
