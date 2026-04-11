<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use CafeCulture\BlogBundle\Entity\Article;
use CafeCulture\BlogBundle\Entity\Category;
use CafeCulture\BlogBundle\Entity\Tag;

class CafeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ── Catégories ──────────────────────────────────────────────────────
        $categoriesData = [
            ['Origines du Café',        'origines',     '#8B4513', 'fa-globe',     0],
            ['Torréfaction',            'torrefaction', '#C0392B', 'fa-fire',      1],
            ['Méthodes de Préparation', 'preparations', '#2C3E50', 'fa-coffee',    2],
            ['Recettes',                'recettes',     '#E67E22', 'fa-mug-hot',   3],
            ['Histoire & Culture',      'histoire',     '#8E44AD', 'fa-book',      4],
            ['Matériel & Équipement',   'materiel',     '#27AE60', 'fa-wrench',    5],
        ];

        $categories = [];
        foreach ($categoriesData as [$name, $slug, $color, $icon, $pos]) {
            $cat = (new Category())
                ->setName($name)
                ->setSlug($slug)
                ->setColor($color)
                ->setIcon($icon)
                ->setPosition($pos)
                ->setDescription("Découvrez tout sur la catégorie « {$name} » dans notre blog dédié à la culture du café.");
            $manager->persist($cat);
            $categories[$slug] = $cat;
        }

        // ── Tags ─────────────────────────────────────────────────────────────
        $tagsData = [
            'espresso', 'arabica', 'robusta', 'cold-brew', 'aeropress',
            'v60', 'moka', 'french-press', 'latte', 'cappuccino',
            'single-origin', 'blend', 'specialty', 'bio', 'equitable',
        ];

        $tags = [];
        foreach ($tagsData as $tagSlug) {
            $tag = (new Tag())
                ->setName(ucfirst(str_replace('-', ' ', $tagSlug)))
                ->setSlug($tagSlug);
            $manager->persist($tag);
            $tags[$tagSlug] = $tag;
        }

        // ── Articles ─────────────────────────────────────────────────────────
        $articlesData = [
            [
                'title'    => 'L\'Éthiopie, berceau du café : voyage aux origines',
                'slug'     => 'ethiopie-berceau-du-cafe',
                'category' => 'origines',
                'origin'   => 'Éthiopie',
                'aroma'    => ['fruité', 'floral', 'agrumes'],
                'tags'     => ['arabica', 'single-origin', 'specialty'],
                'featured' => true,
                'content'  => "L'Éthiopie est universellement reconnue comme le pays d'origine du caféier (Coffea arabica). 
La légende de Kaldi, un berger de la région de Kaffa, raconte qu'il aurait découvert les propriétés stimulantes 
du café en observant ses chèvres gambader après avoir mangé des cerises rouges d'un arbuste mystérieux.

Les cafés éthiopiens se distinguent par leurs profils aromatiques d'une remarquable complexité : notes florales 
de jasmin et bergamote, acidité vive d'agrumes, parfois des touches de myrtille ou de pêche. Les régions 
productrices – Yirgacheffe, Sidama, Guji, Harrar – offrent chacune une palette aromatique unique.

La cérémonie du café éthiopienne (\"Bunna Maflat\") est inscrite au patrimoine immatériel de l'UNESCO. 
Elle se déroule en trois services : Abol (premier, le plus fort), Tona (deuxième) et Baraka (troisième, 
signifiant bénédiction). Cette cérémonie symbolise l'hospitalité et le lien social.",
                'excerpt'  => 'Partez à la découverte de l\'Éthiopie, berceau légendaire du café arabica, et de ses terroirs d\'exception.',
                'reading'  => 7,
            ],
            [
                'title'    => 'La torréfaction artisanale : l\'art de sublimer le grain',
                'slug'     => 'torrefaction-artisanale-art-sublimer-grain',
                'category' => 'torrefaction',
                'origin'   => null,
                'aroma'    => ['chocolaté', 'caramel', 'noisette'],
                'tags'     => ['specialty', 'arabica'],
                'featured' => true,
                'content'  => "La torréfaction est la transformation à la chaleur du café vert en café torréfié prêt à la consommation. 
C'est une étape cruciale qui détermine en grande partie le profil aromatique final du café.

Durant la torréfaction, plusieurs réactions chimiques majeures se produisent : la réaction de Maillard 
(à l'origine des arômes grillés et caramélisés), la caramélisation des sucres, et la pyrolyse qui développe 
les huiles essentielles. Le fameux \"crack\" (premier crack vers 196°C, second crack vers 224°C) indique 
les paliers importants du processus.

Les niveaux de torréfaction se distinguent : claire (Blonde/Light Roast) pour préserver les arômes floraux 
et fruités, médium (City/Full City) pour un équilibre acidité-corps, foncée (Dark/French Roast) pour 
un profil corsé et des notes de chocolat amer. Le torréfacteur artisanal adapte chaque profil au grain.",
                'excerpt'  => 'Comprendre la torréfaction, de la réaction de Maillard au crack, pour mieux apprécier votre tasse.',
                'reading'  => 8,
            ],
            [
                'title'    => 'Le V60 : la méthode pour un café filtré d\'exception',
                'slug'     => 'v60-methode-cafe-filtre-exception',
                'category' => 'preparations',
                'origin'   => 'Colombie',
                'aroma'    => ['fruité', 'agrumes', 'floral'],
                'tags'     => ['v60', 'single-origin', 'arabica'],
                'featured' => false,
                'content'  => "Le V60 de Hario (une marque japonaise) est devenu un incontournable du café de spécialité. 
Son design en V à 60 degrés, ses rainures en spirale et son large trou unique permettent un contrôle 
précis du temps d'extraction.

Recette de base pour 1 tasse (300 ml) :
- 18 g de café moulu moyen-fin (taille : sucre cristal)
- 300 ml d'eau filtrée à 92-94°C
- Ratio café/eau : 1:16

Technique : Pré-infusez avec 36 ml d'eau pendant 30 secondes (bloom), puis versez l'eau en cercles 
concentriques en 2-3 fois, sur un total de 2min30 à 3min. La propreté en tasse est remarquable, 
laissant les notes fruités et florales s'exprimer pleinement.",
                'excerpt'  => 'Guide complet pour maîtriser la méthode V60 et révéler toute la complexité de vos cafés de spécialité.',
                'reading'  => 6,
            ],
            [
                'title'    => 'Recette du Dalgona Coffee : le café fouetté coréen',
                'slug'     => 'recette-dalgona-coffee-cafe-fouette-coreen',
                'category' => 'recettes',
                'origin'   => null,
                'aroma'    => ['caramel', 'chocolaté'],
                'tags'     => ['cappuccino', 'latte'],
                'featured' => false,
                'content'  => "Le Dalgona Coffee (달고나 커피) a envahi les réseaux sociaux en 2020. Son nom vient d'une 
confiserie coréenne au goût caramélisé. La préparation est simple mais spectaculaire.

Ingrédients pour 2 verres :
- 2 c. à soupe de café soluble (instantané)
- 2 c. à soupe de sucre blanc
- 2 c. à soupe d'eau chaude
- 300 ml de lait (chaud ou froid, au choix)
- Glaçons (optionnel)

Préparation : Fouettez énergiquement le café, le sucre et l'eau pendant 5 à 10 minutes (ou 2 min au 
batteur électrique) jusqu'à obtenir une mousse crémeuse et aérée. Versez le lait dans un grand verre, 
déposez la mousse dessus. Mélangez avant de déguster !",
                'excerpt'  => 'La recette virale du Dalgona Coffee, café fouetté coréen crémeux à réaliser en quelques minutes.',
                'reading'  => 4,
            ],
            [
                'title'    => 'Histoire du café : de l\'Arabie aux cafés parisiens',
                'slug'     => 'histoire-cafe-arabie-cafes-parisiens',
                'category' => 'histoire',
                'origin'   => 'Yémen',
                'aroma'    => ['épicé', 'terreux'],
                'tags'     => ['arabica', 'specialty'],
                'featured' => true,
                'content'  => "L'histoire du café est intimement liée à celle de la civilisation. Après sa découverte en Éthiopie, 
le café fut d'abord cultivé au Yémen au XVe siècle. Les moines soufis de Mocha (Al-Mokha) utilisaient 
la boisson pour rester éveillés durant leurs prières nocturnes.

Les premiers cafés (qahveh khaneh) ouvrirent à La Mecque et Constantinople au XVIe siècle. Lieux de 
débat intellectuel et politique, ils furent parfois interdits par les autorités craignant leur influence 
sur l'opinion publique.

En France, le Café de Procope ouvrit ses portes à Paris en 1686 — il existe encore aujourd'hui. 
Voltaire, Rousseau, et plus tard les révolutionnaires y débattirent des idées qui allaient transformer 
le monde. Le café devint le symbole du siècle des Lumières.",
                'excerpt'  => 'Retracez l\'incroyable voyage du café, des plateaux éthiopiens aux cafés littéraires parisiens du XVIIe siècle.',
                'reading'  => 9,
            ],
            [
                'title'    => 'Choisir son moulin à café : burr vs lames, le guide complet',
                'slug'     => 'choisir-moulin-cafe-burr-vs-lames',
                'category' => 'materiel',
                'origin'   => null,
                'aroma'    => null,
                'tags'     => ['espresso', 'french-press', 'aeropress'],
                'featured' => false,
                'content'  => "Le moulin est l'équipement le plus important pour préparer un bon café — même plus que la machine. 
Une mouture homogène est la clé d'une extraction réussie.

Les moulins à lames (blade grinders) hachent les grains de manière irrégulière, produisant une mouture 
hétérogène. Le résultat en tasse est décevant : sur-extraction des particules fines (amertume) et 
sous-extraction des grosses (acidité). À éviter.

Les moulins à meules (burr grinders) écrasent le grain entre deux surfaces, produisant une mouture 
régulière et ajustable. On distingue les meules coniques (plus silencieuses, moins de chaleur) des 
meules plates (plus précises, coût supérieur).

Notre sélection par budget :
- Entrée de gamme : Timemore C2 (~60€), excellent rapport qualité/prix
- Milieu de gamme : 1Zpresso JX (~130€), idéal pour espresso et filtre
- Haut de gamme : Commandante C40 (~300€), la référence des passionnés",
                'excerpt'  => 'Meules ou lames ? Coniques ou plates ? Tout savoir pour choisir le moulin adapté à votre méthode de préparation.',
                'reading'  => 7,
            ],
        ];

        foreach ($articlesData as $data) {
            $article = (new Article())
                ->setTitle($data['title'])
                ->setSlug($data['slug'])
                ->setContent($data['content'])
                ->setExcerpt($data['excerpt'])
                ->setCategory($categories[$data['category']])
                ->setCoffeeOrigin($data['origin'])
                ->setAromaProfile($data['aroma'])
                ->setFeatured($data['featured'])
                ->setReadingTimeMinutes($data['reading'])
                ->setAuthorName('L\'Équipe Café Culture')
                ->setMetaTitle($data['title'] . ' | La Culture du Café')
                ->setMetaDescription($data['excerpt'])
                ->publish();

            foreach ($data['tags'] as $tagSlug) {
                if (isset($tags[$tagSlug])) {
                    $article->addTag($tags[$tagSlug]);
                }
            }

            $manager->persist($article);
        }

        $manager->flush();
    }
}
