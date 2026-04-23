<?php

namespace App\Entity\Shipment;

use App\Enum\CarrierCode;
use App\Repository\Shipment\CarrierRepository;
use App\Trait\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarrierRepository::class)]
class Carrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trackingUrlTemplate = null;

    #[ORM\Column(type: 'string', length: 50, unique: true, enumType: CarrierCode::class)]
    private CarrierCode $technicalName;

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
        $this->name = strtoupper($name);

        return $this;
    }

    public function getTrackingUrlTemplate(): ?string
    {
        return $this->trackingUrlTemplate;
    }

    public function setTrackingUrlTemplate(?string $trackingUrlTemplate): static
    {
        $this->trackingUrlTemplate = $trackingUrlTemplate;

        return $this;
    }

    public function getTechnicalName(): CarrierCode
    {
        return $this->technicalName;
    }

    public function setTechnicalName(CarrierCode $technicalName): static
    {
        $this->technicalName = $technicalName;

        return $this;
    }
}
