<?php

namespace App\DataFixtures;

use App\Entity\User\User;
use App\Entity\Product\Wood;
use App\Entity\Product\Product;
use Symfony\Component\Uid\Uuid;
use App\Entity\Order\OrderStatus;
use App\Entity\Product\Category;
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

        // Create Categories
        $this->createCategories($manager);
        $manager->flush();

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

    private function getCategories(ObjectManager $manager): array
    {
        return $manager->getRepository(Category::class)->findAll();
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

    private function createCategories(ObjectManager $manager): void
    {
        $categories = [
            [   // 0
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1AAA',
                'name' => 'Décoration',
            ],
            [   // 1
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1BAA',
                'name' => 'Cuisine',
            ],
            [   // 2
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1CAA',
                'name' => 'Rangement et organisation',
            ],
            [   // 3
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1DAA',
                'name' => 'Entrée',
            ],
            [   // 4
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1EAA',
                'name' => 'Bureau',
            ],
            [   // 5
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1FAA',
                'name' => 'Salle de bain',
            ],
            [   // 6
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1GAA',
                'name' => 'Accessoires',
            ],
            [   // 7
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1HAA',
                'name' => 'Salon',
            ]
        ];
        foreach ($categories as $c) {
            $category = new Category();
            $category->setName($c['name'])
                ->setPublicId($c['publicId']);
            $manager->persist($category);
        }
    }

    private function createProducts(ObjectManager $manager): void
    {
        $categories = $this->getCategories($manager);

        $products = [
            [ // 0
                'name' => 'Tabouret en bois',
                'description' => 'Tabouret artisanal, assemblage tenon-mortaise, finition huile dure. Stable et compact, idéal pour un usage quotidien.',
                'category' => $categories[4],
            ],
            [ // 1
                'name' => 'Planche de découpe premium',
                'description' => 'Planche épaisse avec chanfreins, résistante à l’humidité, idéale pour cuisine ou service. Finition huile alimentaire.',
                'category' => $categories[1],
            ],
            [ // 2
                'name' => 'Étagère murale flottante',
                'description' => 'Étagère minimaliste avec fixations invisibles. Parfaite pour salon, cuisine ou bureau. Surface légèrement brossée.',
                'category' => $categories[2],
            ],
            [ // 3
                'name' => 'Table basse artisanale',
                'description' => 'Table basse robuste, plateau massif, pieds trapèze, finition cire naturelle. Style sobre et moderne.',
                'category' => $categories[7],
            ],
            [ // 4
                'name' => 'Banc d’entrée compact',
                'description' => 'Banc d’intérieur avec assise confortable, idéal pour chausser/déchausser. Structure renforcée, finition satinée.',
                'category' => $categories[3],
            ],
            [ // 5
                'name' => 'Boîte de rangement décorative',
                'description' => 'Petite boîte traditionnelle à assemblage à queues-d’aronde. Idéale pour bijoux, rangement de bureau ou objets précieux.',
                'category' => $categories[2],
            ],
            [ // 6
                'name' => 'Lampe d’ambiance en bois massif',
                'description' => 'Corps en bois tourné, vernis satiné, câble textile. Apporte une lumière chaude, idéale pour une chambre ou un bureau.',
                'category' => $categories[7],
            ],
            [ // 7
                'name' => 'Plateau de service rectangulaire',
                'description' => 'Plateau large avec poignées intégrées. Parfait pour cuisine, petit-déjeuner au lit ou service de boissons.',
                'category' => $categories[1],
            ],
            [ // 8
                'name' => 'Porte-revues mural en bois',
                'description' => 'Élément mural ajouré, permettant de ranger magazines, livres fins ou journaux. Finition huile naturelle.',
                'category' => $categories[0],
            ],
            [ // 9
                'name' => 'Horloge murale artisanale',
                'description' => 'Horloge silencieuse, disque en bois tourné, aiguilles en métal noir. Style minimaliste et chaleureux.',
                'category' => $categories[0],
            ],
            [ // 10
                'name' => 'Porte-couteaux magnétique',
                'description' => 'Barre murale magnétique en bois massif, idéale pour ranger les couteaux dans la cuisine.',
                'category' => $categories[1],
            ],
            [ // 11
                'name' => 'Caisse de rangement empilable',
                'description' => 'Caisse ajourée, robuste, conçue pour être empilée. Parfaite pour atelier, bureau ou chambre.',
                'category' => $categories[2],
            ],
            [ // 12
                'name' => 'Cadre photo massif',
                'description' => 'Cadre en bois massif, bords arrondis, verre anti-reflets. Disponible en plusieurs essences.',
                'category' => $categories[0],
            ],
            [ // 13
                'name' => 'Porte-plantes sur pied',
                'description' => 'Support élégant pour plantes, avec structure renforcée et finition vernie.',
                'category' => $categories[0],
            ],
        ];

        foreach ($products as $p) {
            $product = new Product();
            $product->setName($p['name'])
                ->setDescription($p['description'])
                ->setCategoryId($p['category']);
            $manager->persist($product);
        }
    }

    private function createProductVariants(ObjectManager $manager): void
    {
        $products = $this->getProducts($manager);
        $woodTypes = $this->getWoodTypes($manager);

        $productVariants = [
            [ // 0
                'product' => $products[0],
                'wood' => $woodTypes[0],
                'publicId' => '7Qf3sG9Lk2PzW81cNhA5',
                'isDefault' => true,
                'price' => 950,
                'stock' => 20,
            ],
            [ // 0
                'product' => $products[0],
                'wood' => $woodTypes[1],
                'publicId' => '3Dz7LmJ1qR8TbP0fVwKe',
                'isDefault' => false,
                'price' => 650,
                'stock' => 35,
            ],
            [ // 0
                'product' => $products[0],
                'wood' => $woodTypes[6],
                'publicId' => 'Zp1Kx8Fr2M5tQ0AeLu93',
                'isDefault' => false,
                'price' => 850,
                'stock' => 22,
            ],

            [ // 1
                'product' => $products[1],
                'wood' => $woodTypes[5],
                'publicId' => 'aS93FjK72Px1tVQm8LnC',
                'isDefault' => true,
                'price' => 1800,
                'stock' => 15,
            ],
            [ // 1
                'product' => $products[1],
                'wood' => $woodTypes[4],
                'publicId' => 'bP0xTn92FrL6cQw1Je78',
                'isDefault' => false,
                'price' => 2100,
                'stock' => 12,
            ],
            [ // 1
                'product' => $products[1],
                'wood' => $woodTypes[2],
                'publicId' => 'Lk8Df12BnW9sEe5Qr7Ut',
                'isDefault' => false,
                'price' => 1300,
                'stock' => 25,
            ],

            [ // 2
                'product' => $products[2],
                'wood' => $woodTypes[0],
                'publicId' => 'Wm5Qe9L2aK8fTz13SbCd',
                'isDefault' => true,
                'price' => 3200,
                'stock' => 10,
            ],
            [ // 2
                'product' => $products[2],
                'wood' => $woodTypes[1],
                'publicId' => 'Nv4HsX7kP0wAa93LuQFe',
                'isDefault' => false,
                'price' => 2100,
                'stock' => 18,
            ],
            [ // 2
                'product' => $products[2],
                'wood' => $woodTypes[7],
                'publicId' => 'Rt7YpM2LfQ6bEw04JxSa',
                'isDefault' => false,
                'price' => 3500,
                'stock' => 8,
            ],

            [ // 3
                'product' => $products[3],
                'wood' => $woodTypes[0],
                'publicId' => 'Fq8R9aW1zLk0TpXm3Be7',
                'isDefault' => true,
                'price' => 18900,
                'stock' => 5,
            ],
            [ // 3
                'product' => $products[3],
                'wood' => $woodTypes[6],
                'publicId' => 'Gp2Bd7QxC1eT8Lv4Mf90',
                'isDefault' => false,
                'price' => 16500,
                'stock' => 6,
            ],
            [ // 3
                'product' => $products[3],
                'wood' => $woodTypes[4],
                'publicId' => 'Kx1Uw93RzJm8Fq2Hs7Pa',
                'isDefault' => false,
                'price' => 12500,
                'stock' => 4,
            ],

            [ // 4
                'product' => $products[4],
                'wood' => $woodTypes[0],
                'publicId' => 'Qe9P1sMb4Lk7Tw2DaVxH',
                'isDefault' => true,
                'price' => 18900,
                'stock' => 7,
            ],
            [ // 4
                'product' => $products[4],
                'wood' => $woodTypes[2],
                'publicId' => 'Mn4Vx8Qf1R0aKe9LpTzC',
                'isDefault' => false,
                'price' => 9900,
                'stock' => 10,
            ],
            [ // 4
                'product' => $products[4],
                'wood' => $woodTypes[5],
                'publicId' => 'Ts3Bf6Wn9Dp2Lm0QeAaX',
                'isDefault' => false,
                'price' => 11800,
                'stock' => 8,
            ],

            [ // 5
                'product' => $products[5],
                'wood' => $woodTypes[7],
                'publicId' => 'Uq4Kp1Mn8Ls0Tb3VwR9e',
                'isDefault' => true,
                'price' => 2500,
                'stock' => 18,
            ],
            [ // 5
                'product' => $products[5],
                'wood' => $woodTypes[0],
                'publicId' => 'Hp6Vd2Sa1Wx7Lm9Qe0Fb',
                'isDefault' => false,
                'price' => 2700,
                'stock' => 15,
            ],
            [ // 5
                'product' => $products[5],
                'wood' => $woodTypes[3],
                'publicId' => 'Dx0Lw8Bn4Jp3Rk6Se2Mf',
                'isDefault' => false,
                'price' => 3400,
                'stock' => 10,
            ],

            [ // 6
                'product' => $products[6],
                'wood' => $woodTypes[6],
                'publicId' => 'Lp7Xw3Qa0Fn9Sg4Me1Tr',
                'isDefault' => true,
                'price' => 4800,
                'stock' => 14,
            ],
            [ // 6
                'product' => $products[6],
                'wood' => $woodTypes[7],
                'publicId' => 'Cg2Mv8Lt1Qp5Rw0DbSx9',
                'isDefault' => false,
                'price' => 5200,
                'stock' => 12,
            ],
            [ // 6
                'product' => $products[6],
                'wood' => $woodTypes[5],
                'publicId' => 'Sf8Pa0Km3Xe9Wt1HbQ4L',
                'isDefault' => false,
                'price' => 5400,
                'stock' => 10,
            ],

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

        for ($i = 0; $i < count($productVariants); $i++) {
            $image = new Image();
            $image->setFolderName("Vw3Ln9Qb5Pe2Sa0HxF7A")
                ->setImageName("Vw3Ln9Qb5Pe2Sa0HxF7B")
                ->setFormat("webp")
                ->setIsDefault($i % 3 === 0 ? true : false)
                ->setProductVariantId($productVariants[$i]);

            $manager->persist($image);
        }
    }
}
