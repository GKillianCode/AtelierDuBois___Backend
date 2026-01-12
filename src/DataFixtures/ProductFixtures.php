<?php

namespace App\DataFixtures;

use App\Entity\Product\Wood;
use App\Entity\Product\Image;
use App\Entity\Product\Product;
use App\Entity\Product\Category;
use App\Entity\Product\ProductReview;
use App\Entity\Product\ProductVariant;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const WOOD_REFERENCE = 'wood';
    public const CATEGORY_REFERENCE = 'category';
    public const PRODUCT_REFERENCE = 'product';
    public const PRODUCT_VARIANT_REFERENCE = 'product_variant';

    /**
     * Creates sample products, categories, wood types, variants, images and reviews
     */
    public function load(ObjectManager $manager): void
    {
        $this->createWoodTypes($manager);
        $this->createCategories($manager);
        $manager->flush();

        $this->createProducts($manager);
        $manager->flush();

        $this->createProductVariants($manager);
        $manager->flush();

        $this->createImagesForProductVariants($manager);
        $manager->flush();

        $this->createProductReviews($manager);
        $manager->flush();
    }

    /**
     * Creates sample wood types
     */
    private function createWoodTypes(ObjectManager $manager): void
    {
        $woods = ["CHENE", "PIN", "BOULEAU", "BOIS DE ROSE", "TECK", "ACACIA", "HETRE", "CERISIER"];
        foreach ($woods as $index => $w) {
            $woodType = new Wood();
            $woodType->setName($w);
            $manager->persist($woodType);
            $this->addReference(self::WOOD_REFERENCE . '_' . $index, $woodType);
        }
    }

    /**
     * Creates sample product categories
     */
    private function createCategories(ObjectManager $manager): void
    {
        $categories = [
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1AAA',
                'name' => 'Décoration',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1BAA',
                'name' => 'Cuisine',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1CAA',
                'name' => 'Rangement et organisation',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1DAA',
                'name' => 'Entrée',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1EAA',
                'name' => 'Bureau',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1FAA',
                'name' => 'Salle de bain',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1GAA',
                'name' => 'Accessoires',
            ],
            [
                'publicId' => 'Vw3Ln9Qb5Pe2Sa0HxF1HAA',
                'name' => 'Salon',
            ]
        ];

        foreach ($categories as $index => $c) {
            $category = new Category();
            $category->setName($c['name'])
                ->setPublicId($c['publicId']);
            $manager->persist($category);
            $this->addReference(self::CATEGORY_REFERENCE . '_' . $index, $category);
        }
    }

    /**
     * Creates sample products
     */
    private function createProducts(ObjectManager $manager): void
    {
        $products = [
            [
                'name' => 'Tabouret en bois',
                'description' => 'Tabouret artisanal, assemblage tenon-mortaise, finition huile dure. Stable et compact, idéal pour un usage quotidien.',
                'category' => 4,
            ],
            [
                'name' => 'Planche de découpe premium',
                'description' => 'Planche épaisse avec chanfreins, résistante à l\'humidité, idéale pour cuisine ou service. Finition huile alimentaire.',
                'category' => 1,
            ],
            [
                'name' => 'Étagère murale flottante',
                'description' => 'Étagère minimaliste avec fixations invisibles. Parfaite pour salon, cuisine ou bureau. Surface légèrement brossée.',
                'category' => 2,
            ],
            [
                'name' => 'Table basse artisanale',
                'description' => 'Table basse robuste, plateau massif, pieds trapèze, finition cire naturelle. Style sobre et moderne.',
                'category' => 7,
            ],
            [
                'name' => 'Banc d\'entrée compact',
                'description' => 'Banc d\'intérieur avec assise confortable, idéal pour chausser/déchausser. Structure renforcée, finition satinée.',
                'category' => 3,
            ],
            [
                'name' => 'Boîte de rangement décorative',
                'description' => 'Petite boîte traditionnelle à assemblage à queues-d\'aronde. Idéale pour bijoux, rangement de bureau ou objets précieux.',
                'category' => 2,
            ],
            [
                'name' => 'Lampe d\'ambiance en bois massif',
                'description' => 'Corps en bois tourné, vernis satiné, câble textile. Apporte une lumière chaude, idéale pour une chambre ou un bureau.',
                'category' => 7,
            ],
            [
                'name' => 'Plateau de service rectangulaire',
                'description' => 'Plateau large avec poignées intégrées. Parfait pour cuisine, petit-déjeuner au lit ou service de boissons.',
                'category' => 1,
            ],
            [
                'name' => 'Porte-revues mural en bois',
                'description' => 'Élément mural ajouré, permettant de ranger magazines, livres fins ou journaux. Finition huile naturelle.',
                'category' => 0,
            ],
            [
                'name' => 'Horloge murale artisanale',
                'description' => 'Horloge silencieuse, disque en bois tourné, aiguilles en métal noir. Style minimaliste et chaleureux.',
                'category' => 0,
            ],
            [
                'name' => 'Porte-couteaux magnétique',
                'description' => 'Barre murale magnétique en bois massif, idéale pour ranger les couteaux dans la cuisine.',
                'category' => 1,
            ],
            [
                'name' => 'Caisse de rangement empilable',
                'description' => 'Caisse ajourée, robuste, conçue pour être empilée. Parfaite pour atelier, bureau ou chambre.',
                'category' => 2,
            ],
            [
                'name' => 'Cadre photo massif',
                'description' => 'Cadre en bois massif, bords arrondis, verre anti-reflets. Disponible en plusieurs essences.',
                'category' => 0,
            ],
            [
                'name' => 'Porte-plantes sur pied',
                'description' => 'Support élégant pour plantes, avec structure renforcée et finition vernie.',
                'category' => 0,
            ],
        ];

        foreach ($products as $index => $p) {
            $categories = $manager->getRepository(Category::class)->findAll();
            if (empty($categories) || !isset($categories[$p['category']])) {
                continue;
            }

            $product = new Product();
            $product->setName($p['name'])
                ->setDescription($p['description'])
                ->setCategoryId($categories[$p['category']]);
            $manager->persist($product);
            $this->addReference(self::PRODUCT_REFERENCE . '_' . $index, $product);
        }
    }

    /**
     * Creates sample product variants with different wood types and pricing
     */
    private function createProductVariants(ObjectManager $manager): void
    {
        $productVariants = [
            [
                'product' => 0,
                'wood' => 0,
                'publicId' => '7Qf3sG9Lk2PzW81cNhA5',
                'isDefault' => true,
                'price' => 950,
                'stock' => 20,
            ],
            [
                'product' => 0,
                'wood' => 1,
                'publicId' => '3Dz7LmJ1qR8TbP0fVwKe',
                'isDefault' => false,
                'price' => 650,
                'stock' => 35,
            ],
            [
                'product' => 0,
                'wood' => 6,
                'publicId' => 'Zp1Kx8Fr2M5tQ0AeLu93',
                'isDefault' => false,
                'price' => 850,
                'stock' => 22,
            ],
            [
                'product' => 1,
                'wood' => 5,
                'publicId' => 'aS93FjK72Px1tVQm8LnC',
                'isDefault' => true,
                'price' => 1800,
                'stock' => 15,
            ],
            [
                'product' => 1,
                'wood' => 4,
                'publicId' => 'bP0xTn92FrL6cQw1Je78',
                'isDefault' => false,
                'price' => 2100,
                'stock' => 12,
            ],
            [
                'product' => 1,
                'wood' => 2,
                'publicId' => 'Lk8Df12BnW9sEe5Qr7Ut',
                'isDefault' => false,
                'price' => 1300,
                'stock' => 25,
            ],
            [
                'product' => 2,
                'wood' => 0,
                'publicId' => 'Wm5Qe9L2aK8fTz13SbCd',
                'isDefault' => true,
                'price' => 3200,
                'stock' => 10,
            ],
            [
                'product' => 2,
                'wood' => 1,
                'publicId' => 'Nv4HsX7kP0wAa93LuQFe',
                'isDefault' => false,
                'price' => 2100,
                'stock' => 18,
            ],
            [
                'product' => 2,
                'wood' => 7,
                'publicId' => 'Rt7YpM2LfQ6bEw04JxSa',
                'isDefault' => false,
                'price' => 3500,
                'stock' => 8,
            ],
            [
                'product' => 3,
                'wood' => 0,
                'publicId' => 'Fq8R9aW1zLk0TpXm3Be7',
                'isDefault' => true,
                'price' => 18900,
                'stock' => 5,
            ],
            [
                'product' => 3,
                'wood' => 6,
                'publicId' => 'Gp2Bd7QxC1eT8Lv4Mf90',
                'isDefault' => false,
                'price' => 16500,
                'stock' => 6,
            ],
            [
                'product' => 3,
                'wood' => 4,
                'publicId' => 'Kx1Uw93RzJm8Fq2Hs7Pa',
                'isDefault' => false,
                'price' => 12500,
                'stock' => 4,
            ],
            [
                'product' => 4,
                'wood' => 0,
                'publicId' => 'Qe9P1sMb4Lk7Tw2DaVxH',
                'isDefault' => true,
                'price' => 18900,
                'stock' => 7,
            ],
            [
                'product' => 4,
                'wood' => 2,
                'publicId' => 'Mn4Vx8Qf1R0aKe9LpTzC',
                'isDefault' => false,
                'price' => 9900,
                'stock' => 10,
            ],
            [
                'product' => 4,
                'wood' => 5,
                'publicId' => 'Ts3Bf6Wn9Dp2Lm0QeAaX',
                'isDefault' => false,
                'price' => 11800,
                'stock' => 8,
            ],
            [
                'product' => 5,
                'wood' => 7,
                'publicId' => 'Uq4Kp1Mn8Ls0Tb3VwR9e',
                'isDefault' => true,
                'price' => 2500,
                'stock' => 18,
            ],
            [
                'product' => 5,
                'wood' => 0,
                'publicId' => 'Hp6Vd2Sa1Wx7Lm9Qe0Fb',
                'isDefault' => false,
                'price' => 2700,
                'stock' => 15,
            ],
            [
                'product' => 5,
                'wood' => 3,
                'publicId' => 'Dx0Lw8Bn4Jp3Rk6Se2Mf',
                'isDefault' => false,
                'price' => 3400,
                'stock' => 10,
            ],
            [
                'product' => 6,
                'wood' => 6,
                'publicId' => 'Lp7Xw3Qa0Fn9Sg4Me1Tr',
                'isDefault' => true,
                'price' => 4800,
                'stock' => 14,
            ],
            [
                'product' => 6,
                'wood' => 7,
                'publicId' => 'Cg2Mv8Lt1Qp5Rw0DbSx9',
                'isDefault' => false,
                'price' => 5200,
                'stock' => 12,
            ],
            [
                'product' => 6,
                'wood' => 5,
                'publicId' => 'Sf8Pa0Km3Xe9Wt1HbQ4L',
                'isDefault' => false,
                'price' => 5400,
                'stock' => 10,
            ],
        ];

        foreach ($productVariants as $index => $pv) {
            $products = $manager->getRepository(Product::class)->findAll();
            $woods = $manager->getRepository(Wood::class)->findAll();

            if (
                empty($products) || empty($woods) ||
                !isset($products[$pv['product']]) || !isset($woods[$pv['wood']])
            ) {
                continue;
            }

            $productVariant = new ProductVariant();
            $productVariant->setProductId($products[$pv['product']])
                ->setWoodId($woods[$pv['wood']])
                ->setPublicId($pv['publicId'])
                ->setIsDefault($pv['isDefault'])
                ->setPrice($pv['price'])
                ->setStock($pv['stock']);
            $manager->persist($productVariant);
            $this->addReference(self::PRODUCT_VARIANT_REFERENCE . '_' . $index, $productVariant);
        }
    }

    /**
     * Creates sample images for product variants
     */
    private function createImagesForProductVariants(ObjectManager $manager): void
    {
        $variantCount = 21;
        for ($i = 0; $i < $variantCount; $i++) {
            $productVariants = $manager->getRepository(ProductVariant::class)->findAll();
            if (empty($productVariants) || !isset($productVariants[$i])) {
                continue;
            }

            $image = new Image();
            $image->setFolderName("Vw3Ln9Qb5Pe2Sa0HxF7A")
                ->setImageName("Vw3Ln9Qb5Pe2Sa0HxF7B")
                ->setFormat("webp")
                ->setIsDefault($i % 3 === 0 ? true : false)
                ->setProductVariantId($productVariants[$i]);

            $manager->persist($image);
        }
    }

    /**
     * Creates sample product reviews from users
     */
    private function createProductReviews(ObjectManager $manager): void
    {
        $users = $this->getUsersFromDatabase($manager);
        $productVariants = $manager->getRepository(\App\Entity\Product\ProductVariant::class)->findAll();
        $totalReviews = min(50, count($users) * 3);

        for ($i = 0; $i < $totalReviews; $i++) {
            if (empty($users) || empty($productVariants)) {
                break;
            }

            $randomUser = $users[array_rand($users)];
            $randomVariant = $productVariants[array_rand($productVariants)];

            $existingReview = $manager->getRepository(ProductReview::class)
                ->findOneBy([
                    'userId' => $randomUser,
                    'productVariantId' => $randomVariant
                ]);

            if ($existingReview) {
                continue;
            }

            $review = new ProductReview();
            $rating = mt_rand(1, 5);

            $comments = [
                1 => ["Très déçu de cet achat", "Qualité décevante", "Ne correspond pas aux attentes"],
                2 => ["Produit moyen", "Quelques défauts", "Pas terrible"],
                3 => ["Correct sans plus", "Produit moyen", "Ça fait le travail"],
                4 => ["Bon produit", "Satisfait de mon achat", "Belle qualité"],
                5 => ["Excellent produit!", "Parfait, je recommande!", "Superbe qualité artisanale"]
            ];

            $comment = $comments[$rating][array_rand($comments[$rating])];

            $review->setUserId($randomUser)
                ->setProductVariantId($randomVariant)
                ->setRating($rating)
                ->setComment($comment);

            $randomDate = $this->getRandomDateTimeImmuable();
            $review->setCreatedAt($randomDate);
            $review->setUpdatedAt($randomDate);

            $manager->persist($review);
        }
    }

    /**
     * Retrieves users from database
     */
    private function getUsersFromDatabase(ObjectManager $manager): array
    {
        return $manager->getRepository(\App\Entity\User\User::class)->findAll();
    }

    /**
     * Generates random DateTime for testing purposes
     */
    private function getRandomDateTimeImmuable(): \DateTimeImmutable
    {
        $year = 2026;
        $month = mt_rand(10, 12);
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        $day = mt_rand(1, 28);
        $hour = mt_rand(9, 22);
        $minute = mt_rand(0, 59);
        $second = mt_rand(0, 59);

        return new \DateTimeImmutable(sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second));
    }

    /**
     * Define dependencies on other fixtures
     */
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
