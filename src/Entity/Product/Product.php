<?php

namespace App\Entity\Product;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Trait\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use App\Repository\Product\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private int $weightInGrams;

    #[ORM\Column(length: 255)]
    private string $lengthInCentimeters;

    #[ORM\Column]
    private int $widthInCentimeters;

    #[ORM\Column]
    private int $heightInCentimeters;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $maxStackSize;

    /**
     * @var Collection<int, ProductVariant>
     */
    #[ORM\OneToMany(targetEntity: ProductVariant::class, mappedBy: 'productId')]
    private Collection $productVariants;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?Category $categoryId = null;

    use TimestampableTrait;

    public function __construct()
    {
        $this->productVariants = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getWeightInGrams(): ?int
    {
        return $this->weightInGrams;
    }

    public function setWeightInGrams(int $weightInGrams): static
    {
        $this->weightInGrams = $weightInGrams;

        return $this;
    }

    public function getLengthInCentimeters(): ?string
    {
        return $this->lengthInCentimeters;
    }

    public function setLengthInCentimeters(string $lengthInCentimeters): static
    {
        $this->lengthInCentimeters = $lengthInCentimeters;

        return $this;
    }

    public function getWidthInCentimeters(): ?int
    {
        return $this->widthInCentimeters;
    }

    public function setWidthInCentimeters(int $widthInCentimeter): static
    {
        $this->widthInCentimeters = $widthInCentimeter;

        return $this;
    }

    public function getHeightInCentimeters(): ?int
    {
        return $this->heightInCentimeters;
    }

    public function setHeightInCentimeters(int $heightInCentimeters): static
    {
        $this->heightInCentimeters = $heightInCentimeters;

        return $this;
    }

    public function getMaxStackSize(): ?int
    {
        return $this->maxStackSize;
    }

    public function setMaxStackSize(int $maxStackSize): static
    {
        $this->maxStackSize = $maxStackSize;

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    public function addProductVariant(ProductVariant $productVariant): static
    {
        if (!$this->productVariants->contains($productVariant)) {
            $this->productVariants->add($productVariant);
            $productVariant->setProductId($this);
        }

        return $this;
    }

    public function removeProductVariant(ProductVariant $productVariant): static
    {
        if ($this->productVariants->removeElement($productVariant)) {
            // set the owning side to null (unless already changed)
            if ($productVariant->getProductId() === $this) {
                $productVariant->setProductId(null);
            }
        }

        return $this;
    }

    public function getCategoryId(): ?Category
    {
        return $this->categoryId;
    }

    public function setCategoryId(?Category $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }
}
