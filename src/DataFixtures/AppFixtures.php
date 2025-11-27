<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Entity\Product\Wood;
use App\Entity\Product\Product;
use Symfony\Component\Uid\Uuid;
use App\Entity\Order\OrderStatus;
use App\Entity\Product\Image;
use App\Entity\Product\ProductVariant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AppFixtures extends Fixture
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Order Statuses
        $this->createOrderStatus($manager);

        // Create Wood Types
        $this->createWoodTypes($manager);

        // Create Products
        $this->createProducts($manager);
        $manager->flush();

        // Create Product Variants
        $this->createProductVariants($manager);
        $manager->flush();

        // Create Images for Product Variants
        $this->createImagesForProductVariants($manager);

        // Create a User
        $user = new User();
        $user->setUuid(Uuid::v4()->toRfc4122())
            ->setFirstname('Killian')
            ->setLastname('Godet')
            ->setEmail('godetkillian@yahoo.com')
            ->setPlainPassword('Abricot2024!');
        $manager->persist($user);

        $manager->flush();
    }

    private function createOrderStatus(ObjectManager $manager): void
    {
        $statuses = ["PENDING", "CONFIRMED", "SHIPPED", "DELIVERED"];
        foreach ($statuses as $s) {
            $orderStatus = new OrderStatus();
            $orderStatus->setName($s);
            $manager->persist($orderStatus);
        }
    }

    private function createWoodTypes(ObjectManager $manager): void
    {
        $woods = ["CHENE", "PIN", "BOULEAU", "BOIS DE ROSE", "TECK", "ACACIA", "HETRE", "CERISIER"];
        foreach ($woods as $w) {
            $woodType = new Wood();
            $woodType->setName($w);
            $manager->persist($woodType);
        }
    }

    private function createProducts(ObjectManager $manager): void
    {
        $products = [
            [ // 0
                'name' => 'Table de salle à manger',
                'description' => 'Une belle table en bois massif pour vos repas en famille.',
            ],
            [ // 1
                'name' => 'Chaise',
                'description' => 'Chaise confortable en bois avec un design élégant.',
            ],
            [ // 2
                'name' => 'Armoire',
                'description' => 'Armoire spacieuse en chêne pour ranger vos vêtements.',
            ],
            [ // 3
                'name' => 'Étagère murale',
                'description' => 'Étagère décorative en bois pour salon ou bureau.',
            ],
            [ // 4
                'name' => 'Bureau artisanal',
                'description' => 'Bureau en bois massif avec finitions artisanales.',
            ],
            [ // 5
                'name' => 'Fenêtre en bois',
                'description' => 'Fenêtre en bois massif sur mesures avec finitions artisanales.',
            ]
        ];

        foreach ($products as $p) {
            $product = new Product();
            $product->setName($p['name'])
                ->setDescription($p['description']);
            $manager->persist($product);
        }
    }

    private function getProducts(ObjectManager $manager): array
    {
        return $manager->getRepository(Product::class)->findAll();
    }

    private function getProductVariants(ObjectManager $manager): array
    {
        return $manager->getRepository(ProductVariant::class)->findAll();
    }

    private function getWoodTypes(ObjectManager $manager): array
    {
        return $manager->getRepository(Wood::class)->findAll();
    }

    private function createProductVariants(ObjectManager $manager): void
    {
        $products = $this->getProducts($manager);
        $woodTypes = $this->getWoodTypes($manager);

        $productVariants = [
            [ // 0
                'product' => $products[0], // Table
                'wood' => $woodTypes[0],
                'publicId' => '537u4s94BWogtNsb8vKCo1',
                'isDefault' => true,
                'price' => 49900,
                'stock' => 10,
            ],
            [ // 1
                'product' => $products[0], // Table
                'wood' => $woodTypes[1],
                'publicId' => '537u4s94BWogtNsb8vKCo2',
                'isDefault' => false,
                'price' => 45900,
                'stock' => 5,
            ],
            [ // 2
                'product' => $products[1], // Chaise
                'wood' => $woodTypes[1],
                'publicId' => '537u4s94BWogtNsb8vKCo3',
                'isDefault' => true,
                'price' => 9900,
                'stock' => 20,
            ],
            [ // 3
                'product' => $products[1], // Chaise
                'wood' => $woodTypes[2],
                'publicId' => '537u4s94BWogtNsb8vKCo4',
                'isDefault' => false,
                'price' => 8900,
                'stock' => 15,
            ],
            [ // 4
                'product' => $products[2], // Armoire
                'wood' => $woodTypes[3],
                'publicId' => 'ARMOIRE-CHENE-005',
                'isDefault' => true,
                'price' => 89900,
                'stock' => 3,
            ],
            [ // 5
                'product' => $products[3], // Étagère
                'wood' => $woodTypes[3],
                'publicId' => 'ETAGERE-PIN-006',
                'isDefault' => true,
                'price' => 15900,
                'stock' => 12,
            ],
            [ // 6
                'product' => $products[4], // Bureau
                'wood' => $woodTypes[1],
                'publicId' => 'BUREAU-TECK-007',
                'isDefault' => true,
                'price' => 65900,
                'stock' => 7,
            ],
            [ // 7
                'product' => $products[4], // Bureau
                'wood' => $woodTypes[3],
                'publicId' => 'BUREAU-CHENE-008',
                'isDefault' => false,
                'price' => 69900,
                'stock' => 4,
            ],
            [ // 8
                'product' => $products[5], // Fenêtre
                'wood' => $woodTypes[1],
                'publicId' => 'FENETRE-TECK-009',
                'isDefault' => true,
                'price' => 0,
                'stock' => null,
            ],
            [ // 9
                'product' => $products[5],
                'wood' => $woodTypes[2],
                'publicId' => 'FENETRE-TECK-009',
                'isDefault' => false,
                'price' => 0,
                'stock' => null,
            ],
            [ // 10
                'product' => $products[5],
                'wood' => $woodTypes[3],
                'publicId' => 'FENETRE-TECK-009',
                'isDefault' => false,
                'price' => 0,
                'stock' => null,
            ]
        ];

        foreach ($productVariants as $pv) {
            $productVariant = new ProductVariant();
            $productVariant->setProductId($pv['product'])
                ->setWoodId($pv['wood'])
                ->setPublicId($pv['publicId'])
                ->setIsDefault($pv['isDefault'])
                ->setPrice($pv['price'])
                ->setStock($pv['stock']);
            $manager->persist($productVariant);
        }
    }

    private function createImagesForProductVariants(ObjectManager $manager): void
    {
        $productVariants = $this->getProductVariants($manager);

        $images = [
            // Images pour le variant 0 (Table en CHENE - isDefault: true)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo1',
                'imageName' => '537u4s94BWogtNsb8vKCo1',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 0,
            ],
            [
                'folderName' => '537u4s94BWogtNsb8vKCo1',
                'imageName' => '537u4s94BWogtNsb8vKCo1',
                'format' => 'webp',
                'isDefault' => false,
                'productVariant' => 0,
            ],
            // Images pour le variant 1 (Table en PIN - isDefault: false)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo2',
                'imageName' => '537u4s94BWogtNsb8vKCo2',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 1,
            ],
            // Images pour le variant 2 (Chaise en PIN - isDefault: true)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 2,
            ],
            // Images pour le variant 3 (Chaise en BOULEAU - isDefault: false)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo4',
                'imageName' => '537u4s94BWogtNsb8vKCo4',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 3,
            ],
            // Images pour le variant 4 (Armoire - isDefault: true)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 4,
            ],
            // Images pour le variant 5 (Étagère - isDefault: true)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 5,
            ],
            // Images pour le variant 6 (Bureau en TECK - isDefault: true)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 6,
            ],
            // Images pour le variant 7 (Bureau en CHENE - isDefault: false)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 7,
            ],
            // Images pour le variant 8 (Fenêtre - isDefault: false)
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => true,
                'productVariant' => 8,
            ],
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => false,
                'productVariant' => 9,
            ],
            [
                'folderName' => '537u4s94BWogtNsb8vKCo3',
                'imageName' => '537u4s94BWogtNsb8vKCo3',
                'format' => 'webp',
                'isDefault' => false,
                'productVariant' => 10,
            ],
        ];

        foreach ($images as $img) {
            $image = new Image();
            $image->setFolderName($img['folderName'])
                ->setImageName($img['imageName'])
                ->setFormat($img['format'])
                ->setIsDefault($img['isDefault'])
                ->setProductVariantId($productVariants[$img['productVariant']]);
            $manager->persist($image);
        }
    }
}
