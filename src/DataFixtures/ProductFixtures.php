<?php

namespace App\DataFixtures;

use App\Entity\Product\Wood;
use App\Entity\Product\Image;
use App\Entity\Product\Product;
use App\Entity\Product\Category;
use App\Entity\Product\ProductReview;
use App\Entity\Product\ProductVariant;
use App\Util\UuidUtil;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const WOOD_REFERENCE = 'wood';
    public const CATEGORY_REFERENCE = 'category';
    public const PRODUCT_REFERENCE = 'product';
    public const PRODUCT_VARIANT_REFERENCE = 'product_variant';

    public function __construct(
        private readonly UuidUtil $uuidUtil,
    ) {}

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
            'Décoration',
            'Cuisine',
            'Rangement et organisation',
            'Entrée',
            'Bureau',
            'Salle de bain',
            'Accessoires',
            'Salon',
        ];

        foreach ($categories as $index => $name) {
            $category = new Category();
            $category->setName($name)
                ->setPublicId($this->uuidUtil->generateUuid62());
            $manager->persist($category);
            $this->addReference(self::CATEGORY_REFERENCE . '_' . $index, $category);
        }
    }

    /**
     * Creates 250 sample products
     */
    private function createProducts(ObjectManager $manager): void
    {
        $categories = $manager->getRepository(Category::class)->findAll();
        $categoryCount = count($categories);

        $baseProducts = [
            // Décoration (0)
            ['name' => 'Horloge murale artisanale', 'description' => 'Horloge silencieuse, disque en bois tourné, aiguilles en métal noir. Style minimaliste et chaleureux.', 'category' => 0, 'weight' => 400, 'l' => 30, 'w' => 30, 'h' => 4, 'stack' => 1],
            ['name' => 'Porte-revues mural', 'description' => 'Élément mural ajouré pour ranger magazines, livres fins ou journaux. Finition huile naturelle.', 'category' => 0, 'weight' => 600, 'l' => 35, 'w' => 25, 'h' => 8, 'stack' => 1],
            ['name' => 'Cadre photo massif', 'description' => 'Cadre en bois massif, bords arrondis, verre anti-reflets. Disponible en plusieurs essences.', 'category' => 0, 'weight' => 350, 'l' => 25, 'w' => 20, 'h' => 2, 'stack' => 8],
            ['name' => 'Porte-plantes sur pied', 'description' => 'Support élégant pour plantes, structure renforcée et finition vernie.', 'category' => 0, 'weight' => 3200, 'l' => 35, 'w' => 35, 'h' => 80, 'stack' => 1],
            ['name' => 'Photophore en bois', 'description' => 'Bougeoir ajouré au motif géométrique, diffuse une lumière chaleureuse. Finition huile naturelle.', 'category' => 0, 'weight' => 200, 'l' => 10, 'w' => 10, 'h' => 12, 'stack' => 6],
            ['name' => 'Vide-poches mural', 'description' => 'Organisateur mural multicase, idéal pour clés, courriers ou petits objets du quotidien.', 'category' => 0, 'weight' => 500, 'l' => 40, 'w' => 8, 'h' => 30, 'stack' => 1],
            ['name' => 'Mobile décoratif en bois', 'description' => 'Mobile suspendu en fines lamelles de bois, effet apaisant au moindre souffle.', 'category' => 0, 'weight' => 150, 'l' => 30, 'w' => 5, 'h' => 45, 'stack' => 2],
            ['name' => 'Lettres décoratives en bois', 'description' => 'Lettres découpées au laser, personnalisables, idéales pour chambre ou bureau.', 'category' => 0, 'weight' => 100, 'l' => 15, 'w' => 3, 'h' => 15, 'stack' => 10],
            ['name' => 'Miroir à cadre bois brut', 'description' => 'Cadre en bois naturel, style rustique, finition cire. S\'intègre dans tous les intérieurs.', 'category' => 0, 'weight' => 1800, 'l' => 60, 'w' => 4, 'h' => 80, 'stack' => 1],
            ['name' => 'Sculpture abstraite en bois', 'description' => 'Pièce unique sculptée à la main, formes organiques, finition huile naturelle.', 'category' => 0, 'weight' => 700, 'l' => 20, 'w' => 15, 'h' => 25, 'stack' => 1],
            ['name' => 'Crochet mural en bois', 'description' => 'Crochet simple ou double, robuste, fixation invisible. Pour manteau, tablier ou sacoche.', 'category' => 0, 'weight' => 120, 'l' => 12, 'w' => 4, 'h' => 8, 'stack' => 10],
            // Cuisine (1)
            ['name' => 'Planche de découpe premium', 'description' => 'Planche épaisse avec chanfreins, résistante à l\'humidité, finition huile alimentaire.', 'category' => 1, 'weight' => 1200, 'l' => 40, 'w' => 25, 'h' => 3, 'stack' => 5],
            ['name' => 'Plateau de service rectangulaire', 'description' => 'Plateau large avec poignées intégrées. Parfait pour cuisine ou petit-déjeuner au lit.', 'category' => 1, 'weight' => 800, 'l' => 50, 'w' => 30, 'h' => 5, 'stack' => 3],
            ['name' => 'Porte-couteaux magnétique', 'description' => 'Barre murale magnétique en bois massif pour ranger les couteaux dans la cuisine.', 'category' => 1, 'weight' => 300, 'l' => 40, 'w' => 5, 'h' => 3, 'stack' => 1],
            ['name' => 'Bol en bois tourné', 'description' => 'Bol de service tourné sur bois massif, finition huile alimentaire. Usage quotidien.', 'category' => 1, 'weight' => 450, 'l' => 20, 'w' => 20, 'h' => 8, 'stack' => 4],
            ['name' => 'Passoire à thé et plateau', 'description' => 'Set plateau et porte-filtre sculpté, sobre et fonctionnel. Finition vernis mat.', 'category' => 1, 'weight' => 350, 'l' => 18, 'w' => 12, 'h' => 6, 'stack' => 4],
            ['name' => 'Support pour tablette cuisine', 'description' => 'Lutrin inclinable en bois, stable, pour tablette ou livre de recettes.', 'category' => 1, 'weight' => 600, 'l' => 25, 'w' => 20, 'h' => 22, 'stack' => 2],
            ['name' => 'Range-couverts en bois', 'description' => 'Organisateur de tiroir en bois massif, compartiments multiples. Finition huile.', 'category' => 1, 'weight' => 700, 'l' => 35, 'w' => 25, 'h' => 5, 'stack' => 3],
            ['name' => 'Mortier et pilon en bois', 'description' => 'Mortier et pilon sculptés dans un seul bloc, robustes et faciles à entretenir.', 'category' => 1, 'weight' => 900, 'l' => 14, 'w' => 14, 'h' => 10, 'stack' => 2],
            ['name' => 'Porte-épices mural', 'description' => 'Support mural pour bocaux épices, structure légère en bois, finition naturelle.', 'category' => 1, 'weight' => 550, 'l' => 45, 'w' => 8, 'h' => 20, 'stack' => 1],
            ['name' => 'Dessous-de-plat en bois', 'description' => 'Dessous-de-plat épais, résiste à la chaleur, bords arrondis. Protection élégante.', 'category' => 1, 'weight' => 400, 'l' => 22, 'w' => 22, 'h' => 2, 'stack' => 6],
            ['name' => 'Set cuillères de service', 'description' => 'Cuillères de service sculptées à la main, finition huile alimentaire. Lot de 3.', 'category' => 1, 'weight' => 250, 'l' => 30, 'w' => 6, 'h' => 3, 'stack' => 8],
            // Rangement et organisation (2)
            ['name' => 'Étagère murale flottante', 'description' => 'Étagère minimaliste avec fixations invisibles. Parfaite pour salon, cuisine ou bureau.', 'category' => 2, 'weight' => 1800, 'l' => 80, 'w' => 20, 'h' => 4, 'stack' => 1],
            ['name' => 'Boîte de rangement décorative', 'description' => 'Boîte à queues-d\'aronde, idéale pour bijoux, rangement de bureau ou objets précieux.', 'category' => 2, 'weight' => 450, 'l' => 20, 'w' => 15, 'h' => 8, 'stack' => 4],
            ['name' => 'Caisse de rangement empilable', 'description' => 'Caisse ajourée, robuste, conçue pour être empilée. Parfaite pour atelier ou chambre.', 'category' => 2, 'weight' => 2200, 'l' => 40, 'w' => 30, 'h' => 25, 'stack' => 6],
            ['name' => 'Bibliothèque murale modulable', 'description' => 'Modules individuels à assembler selon vos besoins. Fixation murale solide.', 'category' => 2, 'weight' => 5500, 'l' => 60, 'w' => 25, 'h' => 30, 'stack' => 1],
            ['name' => 'Casier à chaussures', 'description' => 'Casier empilable en bois, 3 paires par unité. Structure ventilée et légère.', 'category' => 2, 'weight' => 3000, 'l' => 50, 'w' => 30, 'h' => 25, 'stack' => 4],
            ['name' => 'Organisateur de bureau', 'description' => 'Compartiments pour stylos, ciseaux, Post-it et petits accessoires de bureau.', 'category' => 2, 'weight' => 600, 'l' => 20, 'w' => 15, 'h' => 12, 'stack' => 3],
            ['name' => 'Tiroir de rangement sur pied', 'description' => 'Meuble à tiroir compact sur pieds, style scandinave, finition huilée.', 'category' => 2, 'weight' => 4500, 'l' => 40, 'w' => 35, 'h' => 70, 'stack' => 1],
            ['name' => 'Range-journaux et cartons', 'description' => 'Bac à journaux structuré en bois massif, finition naturelle. Design épuré.', 'category' => 2, 'weight' => 1600, 'l' => 35, 'w' => 25, 'h' => 40, 'stack' => 1],
            ['name' => 'Panier à linge en bois', 'description' => 'Panier ajouré avec couvercle amovible, structure ventilée, finition brossée.', 'category' => 2, 'weight' => 4800, 'l' => 50, 'w' => 40, 'h' => 60, 'stack' => 1],
            ['name' => 'Porte-manteaux à 5 crochets', 'description' => 'Barre de 5 crochets en bois massif, robuste, montage mural simple.', 'category' => 2, 'weight' => 900, 'l' => 60, 'w' => 6, 'h' => 10, 'stack' => 1],
            // Entrée (3)
            ['name' => 'Banc d\'entrée compact', 'description' => 'Banc d\'intérieur avec assise confortable, idéal pour chausser/déchausser.', 'category' => 3, 'weight' => 8500, 'l' => 80, 'w' => 35, 'h' => 45, 'stack' => 1],
            ['name' => 'Meuble d\'entrée avec miroir', 'description' => 'Console d\'entrée avec miroir intégré et crochet. Compacte et pratique.', 'category' => 3, 'weight' => 12000, 'l' => 70, 'w' => 30, 'h' => 170, 'stack' => 1],
            ['name' => 'Porte-parapluies en bois', 'description' => 'Support discret pour parapluies et cannes, fond amovible. Finition vernie.', 'category' => 3, 'weight' => 2800, 'l' => 20, 'w' => 20, 'h' => 50, 'stack' => 1],
            ['name' => 'Console d\'entrée fine', 'description' => 'Console étroite idéale dans un couloir, 2 étagères ouvertes. Finition huilée.', 'category' => 3, 'weight' => 7000, 'l' => 90, 'w' => 25, 'h' => 80, 'stack' => 1],
            ['name' => 'Desserte d\'entrée roulante', 'description' => 'Petite desserte sur roulettes avec plateau supérieur et sac rangement.', 'category' => 3, 'weight' => 5500, 'l' => 45, 'w' => 40, 'h' => 75, 'stack' => 1],
            ['name' => 'Cage à chaussures ouverte', 'description' => 'Meuble chaussures 3 niveaux, design ouvert, facilite le rangement quotidien.', 'category' => 3, 'weight' => 6000, 'l' => 60, 'w' => 30, 'h' => 55, 'stack' => 1],
            // Bureau (4)
            ['name' => 'Tabouret en bois', 'description' => 'Tabouret artisanal, assemblage tenon-mortaise, finition huile dure. Stable et compact.', 'category' => 4, 'weight' => 2800, 'l' => 35, 'w' => 35, 'h' => 45, 'stack' => 3],
            ['name' => 'Bureau compact en bois massif', 'description' => 'Bureau sobre avec un tiroir et surface de travail spacieuse. Finition satinée.', 'category' => 4, 'weight' => 22000, 'l' => 120, 'w' => 60, 'h' => 75, 'stack' => 1],
            ['name' => 'Étagère bibliothèque', 'description' => 'Bibliothèque 5 niveaux en bois massif, style classique. Capacité de charge élevée.', 'category' => 4, 'weight' => 30000, 'l' => 80, 'w' => 30, 'h' => 180, 'stack' => 1],
            ['name' => 'Pupitre de lecture', 'description' => 'Pupitre inclinable en bois, hauteur ajustable, pour lecture ou travail debout.', 'category' => 4, 'weight' => 7000, 'l' => 60, 'w' => 50, 'h' => 110, 'stack' => 1],
            ['name' => 'Support écran en bois', 'description' => 'Rehausseur d\'écran avec niche de rangement, finition brossée naturelle.', 'category' => 4, 'weight' => 1200, 'l' => 50, 'w' => 20, 'h' => 10, 'stack' => 2],
            ['name' => 'Chaise de bureau en bois', 'description' => 'Chaise assise bois, dossier ergonomique en lames, style scandinave. Confort durable.', 'category' => 4, 'weight' => 9000, 'l' => 50, 'w' => 50, 'h' => 85, 'stack' => 1],
            ['name' => 'Plateau tournant de bureau', 'description' => 'Lazy susan de bureau pour accessoires, finition naturelle, rotation fluide.', 'category' => 4, 'weight' => 500, 'l' => 25, 'w' => 25, 'h' => 5, 'stack' => 4],
            ['name' => 'Porte-courrier de bureau', 'description' => '3 cases superposées pour trier courrier et documents. Stable et compact.', 'category' => 4, 'weight' => 700, 'l' => 25, 'w' => 30, 'h' => 25, 'stack' => 2],
            // Salle de bain (5)
            ['name' => 'Porte-savon en bois', 'description' => 'Porte-savon strié anti-humidité, drainage naturel. Finition huile de coco.', 'category' => 5, 'weight' => 120, 'l' => 12, 'w' => 9, 'h' => 2, 'stack' => 10],
            ['name' => 'Tablette salle de bain murale', 'description' => 'Tablette compacte avec rebord, fixation murale, finition résistante à l\'humidité.', 'category' => 5, 'weight' => 800, 'l' => 50, 'w' => 12, 'h' => 4, 'stack' => 1],
            ['name' => 'Étagère sous-vasque', 'description' => 'Étagère à 2 niveaux, s\'adapte sous la plupart des vasques. Finition imperméable.', 'category' => 5, 'weight' => 2200, 'l' => 50, 'w' => 35, 'h' => 50, 'stack' => 1],
            ['name' => 'Tabouret de bain', 'description' => 'Petit tabouret résistant à l\'humidité, surface antidérapante, idéal en douche.', 'category' => 5, 'weight' => 1800, 'l' => 30, 'w' => 30, 'h' => 40, 'stack' => 2],
            ['name' => 'Porte-serviettes mural', 'description' => 'Barre double murale pour serviettes, finition brossée, montage rapide.', 'category' => 5, 'weight' => 600, 'l' => 55, 'w' => 6, 'h' => 14, 'stack' => 1],
            ['name' => 'Miroir de courtoisie en bois', 'description' => 'Miroir sur pied pivotant, encadrement bois massif, base solide. Style éco.', 'category' => 5, 'weight' => 3500, 'l' => 35, 'w' => 20, 'h' => 50, 'stack' => 1],
            ['name' => 'Organisateur salle de bain', 'description' => 'Bloc multi-compartiments pour crayons, brosses ou flacons. Finition vernie.', 'category' => 5, 'weight' => 400, 'l' => 15, 'w' => 10, 'h' => 12, 'stack' => 4],
            // Accessoires (6)
            ['name' => 'Porte-clés mural en bois', 'description' => 'Plaquette murale avec 4 crochets et tiroir. Idéale pour entrée ou bureau.', 'category' => 6, 'weight' => 300, 'l' => 25, 'w' => 5, 'h' => 15, 'stack' => 3],
            ['name' => 'Porte-montre en bois', 'description' => 'Support individuel ou duo, surface feutrée, finition vernie. Coffret élégant.', 'category' => 6, 'weight' => 180, 'l' => 10, 'w' => 8, 'h' => 8, 'stack' => 6],
            ['name' => 'Valet de nuit en bois', 'description' => 'Valet de chambre avec porte-pantalon, plateau et crochets. Finition satinée.', 'category' => 6, 'weight' => 4000, 'l' => 45, 'w' => 35, 'h' => 110, 'stack' => 1],
            ['name' => 'Porte-bijoux arbre', 'description' => 'Arbre à bijoux en bois massif, branches multiples, base stable. Design naturel.', 'category' => 6, 'weight' => 600, 'l' => 20, 'w' => 15, 'h' => 35, 'stack' => 2],
            ['name' => 'Porte-carte de visite', 'description' => 'Présentoir de bureau, tient 20 cartes, finition naturelle ou teintée.', 'category' => 6, 'weight' => 100, 'l' => 10, 'w' => 7, 'h' => 5, 'stack' => 10],
            ['name' => 'Stylo artisanal en bois tourné', 'description' => 'Stylo bille tourné sur bois précieux, livré en coffret, recharge standard.', 'category' => 6, 'weight' => 40, 'l' => 15, 'w' => 1, 'h' => 1, 'stack' => 20],
            ['name' => 'Sous-verre en bois', 'description' => 'Set de 4 sous-verres en bois massif, anti-glisse, motif gravé au laser.', 'category' => 6, 'weight' => 200, 'l' => 10, 'w' => 10, 'h' => 1, 'stack' => 10],
            ['name' => 'Pochette ceinture en bois', 'description' => 'Étui fine façon pochette, lamelles flexibles assemblées, fermeture clip.', 'category' => 6, 'weight' => 90, 'l' => 16, 'w' => 9, 'h' => 2, 'stack' => 8],
            // Salon (7)
            ['name' => 'Table basse artisanale', 'description' => 'Table basse robuste, plateau massif, pieds trapèze, finition cire naturelle.', 'category' => 7, 'weight' => 15000, 'l' => 120, 'w' => 60, 'h' => 40, 'stack' => 1],
            ['name' => 'Lampe d\'ambiance en bois massif', 'description' => 'Corps en bois tourné, vernis satiné, câble textile. Lumière chaude idéale pour chambre.', 'category' => 7, 'weight' => 1500, 'l' => 15, 'w' => 15, 'h' => 35, 'stack' => 1],
            ['name' => 'Meuble TV en bois massif', 'description' => 'Meuble TV bas avec 2 portes et niche centrale. Câbles dissimulés. Finition huilée.', 'category' => 7, 'weight' => 35000, 'l' => 150, 'w' => 45, 'h' => 50, 'stack' => 1],
            ['name' => 'Canapé en bois et tissu', 'description' => 'Structure en bois massif, coussins déhoussables. Confort et durabilité garantis.', 'category' => 7, 'weight' => 60000, 'l' => 190, 'w' => 85, 'h' => 80, 'stack' => 1],
            ['name' => 'Fauteuil bois et tissu', 'description' => 'Fauteuil à structure bois avec assise rembourrée. Style scandinave, très confortable.', 'category' => 7, 'weight' => 28000, 'l' => 75, 'w' => 75, 'h' => 80, 'stack' => 1],
            ['name' => 'Présentoir à vinyles mural', 'description' => 'Rails muraux pour exposer ses vinyles en décoration. Finition vernie.', 'category' => 7, 'weight' => 700, 'l' => 35, 'w' => 5, 'h' => 5, 'stack' => 4],
            ['name' => 'Table d\'appoint gigogne', 'description' => 'Set de 2 petites tables emboîtables pour le salon. Plateau massif, pieds fins.', 'category' => 7, 'weight' => 6000, 'l' => 45, 'w' => 35, 'h' => 50, 'stack' => 1],
            ['name' => 'Buffet bas 2 portes', 'description' => 'Buffet avec 2 portes coulissantes et intérieur réglable. Finition laquée mate.', 'category' => 7, 'weight' => 40000, 'l' => 140, 'w' => 40, 'h' => 55, 'stack' => 1],
        ];

        $suffixes = [
            'artisanal',
            'premium',
            'naturel',
            'massif',
            'éco',
            'compact',
            'moderne',
            'classique',
            'élégant',
            'robuste',
            'fin',
            'original',
            'rustique',
            'épuré',
            'unique',
            'durable',
            'vintage',
            'brut',
            'poli',
            'huilé',
            'verni',
            'brossé',
            'ciré',
            'teinté',
        ];

        $descriptionVariants = [
            'Fabrication artisanale française, bois sélectionné avec soin.',
            'Assemblage traditionnel tenon-mortaise, sans vis ni colle.',
            'Finition huile naturelle, respectueuse de l\'environnement.',
            'Design épuré et fonctionnel, s\'adapte à tous les intérieurs.',
            'Pièce robuste et durable, transmissible de génération en génération.',
            'Traitement naturel hydrofuge, entretien facile.',
            'Fabriqué à partir de bois issus de forêts gérées durablement.',
            'Style scandinave, lignes sobres et matières nobles.',
        ];

        $index = 0;
        $productCount = count($baseProducts);
        $suffixCount = count($suffixes);
        $descVariantCount = count($descriptionVariants);

        // First pass: create all base products
        foreach ($baseProducts as $p) {
            if (empty($categories) || !isset($categories[$p['category']])) {
                continue;
            }
            $product = new Product();
            $product->setName($p['name'])
                ->setDescription($p['description'])
                ->setCategoryId($categories[$p['category']])
                ->setWeightInGrams($p['weight'])
                ->setLengthInCentimeters($p['l'])
                ->setWidthInCentimeters($p['w'])
                ->setHeightInCentimeters($p['h'])
                ->setMaxStackSize($p['stack']);
            $manager->persist($product);
            $this->addReference(self::PRODUCT_REFERENCE . '_' . $index, $product);
            $index++;
        }

        // Second pass: generate additional products until we reach 250
        $target = 250;
        $generated = 0;
        while ($index < $target) {
            $base = $baseProducts[$generated % $productCount];
            $suffix = $suffixes[$generated % $suffixCount];
            $descExtra = $descriptionVariants[$generated % $descVariantCount];
            $catIndex = ($base['category'] + intdiv($generated, $categoryCount)) % $categoryCount;

            if (empty($categories) || !isset($categories[$catIndex])) {
                $generated++;
                continue;
            }

            $weightVariation = (int) ($base['weight'] * (1 + (($generated % 5) - 2) * 0.08));

            $product = new Product();
            $product->setName($base['name'] . ' ' . $suffix)
                ->setDescription($base['description'] . ' ' . $descExtra)
                ->setCategoryId($categories[$catIndex])
                ->setWeightInGrams(max(50, $weightVariation))
                ->setLengthInCentimeters($base['l'])
                ->setWidthInCentimeters($base['w'])
                ->setHeightInCentimeters($base['h'])
                ->setMaxStackSize($base['stack']);
            $manager->persist($product);
            $this->addReference(self::PRODUCT_REFERENCE . '_' . $index, $product);
            $index++;
            $generated++;
        }
    }

    /**
     * Creates sample product variants with different wood types and pricing
     */
    private function createProductVariants(ObjectManager $manager): void
    {
        $products = $manager->getRepository(Product::class)->findAll();
        $woods    = $manager->getRepository(Wood::class)->findAll();

        if (empty($products) || empty($woods)) {
            return;
        }

        $woodCount = count($woods);

        // Base prices indexed by product position (cycles for generated products)
        $basePrices = [950, 1800, 3200, 18900, 18900, 2500, 4800, 1200, 800, 400, 300, 2200, 350, 3200];

        $variantIndex = 0;

        foreach ($products as $productIndex => $product) {
            $basePrice   = $basePrices[$productIndex % count($basePrices)];
            $defaultWood = $productIndex % $woodCount;

            // Default variant (always present)
            $defaultVariant = new ProductVariant();
            $defaultVariant->setProductId($product)
                ->setWoodId($woods[$defaultWood])
                ->setPublicId($this->uuidUtil->generateUuid62())
                ->setIsDefault(true)
                ->setPrice($basePrice)
                ->setStock(mt_rand(5, 30));
            $manager->persist($defaultVariant);
            $this->addReference(self::PRODUCT_VARIANT_REFERENCE . '_' . $variantIndex, $defaultVariant);
            $variantIndex++;

            // 1 or 2 additional variants for variety
            $extraCount = ($productIndex % 3 === 0) ? 2 : 1;
            for ($e = 1; $e <= $extraCount; $e++) {
                $woodIndex = ($defaultWood + $e) % $woodCount;
                $priceVariation = (int) ($basePrice * (1 + ($e % 3 - 1) * 0.15));

                $extraVariant = new ProductVariant();
                $extraVariant->setProductId($product)
                    ->setWoodId($woods[$woodIndex])
                    ->setPublicId($this->uuidUtil->generateUuid62())
                    ->setIsDefault(false)
                    ->setPrice(max(100, $priceVariation))
                    ->setStock(mt_rand(5, 25));
                $manager->persist($extraVariant);
                $this->addReference(self::PRODUCT_VARIANT_REFERENCE . '_' . $variantIndex, $extraVariant);
                $variantIndex++;
            }
        }
    }

    /**
     * Creates sample images for product variants
     */
    private function createImagesForProductVariants(ObjectManager $manager): void
    {
        $productVariants = $manager->getRepository(ProductVariant::class)->findAll();

        foreach ($productVariants as $variant) {
            $imageCount = mt_rand(2, 4);

            $folderName = $this->uuidUtil->generateUuid62();
            for ($i = 0; $i < $imageCount; $i++) {
                $image = new Image();
                $image->setFolderName($folderName)
                    ->setImageName($this->uuidUtil->generateUuid62())
                    ->setFormat("webp")
                    ->setIsDefault($i === 0)
                    ->setProductVariantId($variant);

                $manager->persist($image);
            }
        }
    }

    /**
     * Creates 0 to 10 reviews per product variant (all variants, default or not)
     */
    private function createProductReviews(ObjectManager $manager): void
    {
        $users = $this->getUsersFromDatabase($manager);
        $productVariants = $manager->getRepository(\App\Entity\Product\ProductVariant::class)->findAll();

        if (empty($users) || empty($productVariants)) {
            return;
        }

        $comments = [
            1 => [
                "Très déçu de cet achat",
                "Qualité décevante",
                "Ne correspond pas aux attentes",
                "Finition décevante pour cette essence",
                "Le bois ne correspond pas à la photo",
                "Assemblage approximatif, je suis déçu",
            ],
            2 => [
                "Produit moyen",
                "Quelques défauts",
                "Pas terrible",
                "Correct mais sans surprise",
                "L'essence de bois est moins jolie en vrai",
                "Quelques imperfections sur la surface",
            ],
            3 => [
                "Correct sans plus",
                "Ça fait le travail",
                "Bon rapport qualité/prix pour cette variante",
                "L'essence choisie est agréable",
                "Commande conforme, rien à redire",
                "Produit correct, livraison rapide",
            ],
            4 => [
                "Bon produit",
                "Satisfait de mon achat",
                "Belle qualité",
                "Très belle essence, je recommande",
                "Le grain du bois est magnifique",
                "Finition soignée, belle pièce",
            ],
            5 => [
                "Excellent produit!",
                "Parfait, je recommande!",
                "Superbe qualité artisanale",
                "Superbe! L'essence est encore plus belle en vrai",
                "Qualité exceptionnelle, artisanat remarquable",
                "Pièce unique, travail d'orfèvre. Bravo!",
            ],
        ];

        $userCount = count($users);

        foreach ($productVariants as $variant) {
            $reviewCount = mt_rand(0, 10);
            if ($reviewCount === 0) {
                continue;
            }

            // Shuffle users to avoid duplicate user+variant reviews
            $shuffledIndices = range(0, $userCount - 1);
            shuffle($shuffledIndices);
            $reviewCount = min($reviewCount, $userCount);

            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users[$shuffledIndices[$i]];
                $rating = mt_rand(1, 5);
                $comment = $comments[$rating][array_rand($comments[$rating])];

                $review = new ProductReview();
                $review->setUserId($user)
                    ->setProductVariantId($variant)
                    ->setRating($rating)
                    ->setComment($comment);

                $randomDate = $this->getRandomDateTimeImmuable();
                $review->setCreatedAt($randomDate);
                $review->setUpdatedAt($randomDate);

                $manager->persist($review);
            }
        }
    }

    /**
     * Retrieves users from database
     * @return \App\Entity\User\User[]
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
        $year = mt_rand(2025, 2026);
        $maxMonth = ($year === 2026) ? 4 : 12;
        $month = mt_rand(1, $maxMonth);
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
