<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'cafe_category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 100, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 7, nullable: true)]
    private ?string $color = null; // Ex: #6B3F2A (marron café)

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $icon = null; // Classe d'icône (FontAwesome, etc.)

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $position = 0;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Article::class)]
    private Collection $articles;

    // Catégories spéciales café
    public const CATEGORIES = [
        'origines'      => ['label' => 'Origines du Café',        'icon' => 'fa-globe',      'color' => '#8B4513'],
        'torrefaction'  => ['label' => 'Torréfaction',            'icon' => 'fa-fire',       'color' => '#C0392B'],
        'preparations'  => ['label' => 'Méthodes de Préparation', 'icon' => 'fa-coffee',     'color' => '#2C3E50'],
        'recettes'      => ['label' => 'Recettes',                'icon' => 'fa-mug-hot',    'color' => '#E67E22'],
        'histoire'      => ['label' => 'Histoire & Culture',      'icon' => 'fa-book',       'color' => '#8E44AD'],
        'materiel'      => ['label' => 'Matériel & Équipement',   'icon' => 'fa-wrench',     'color' => '#27AE60'],
        'varietes'      => ['label' => 'Variétés & Cépages',      'icon' => 'fa-seedling',   'color' => '#16A085'],
        'actualites'    => ['label' => 'Actualités',              'icon' => 'fa-newspaper',  'color' => '#2980B9'],
    ];

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $desc): static { $this->description = $desc; return $this; }

    public function getColor(): ?string { return $this->color; }
    public function setColor(?string $color): static { $this->color = $color; return $this; }

    public function getIcon(): ?string { return $this->icon; }
    public function setIcon(?string $icon): static { $this->icon = $icon; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $pos): static { $this->position = $pos; return $this; }

    public function getArticles(): Collection { return $this->articles; }

    public function getPublishedArticlesCount(): int
    {
        return $this->articles->filter(
            fn(Article $a) => $a->isPublished()
        )->count();
    }

    public function __toString(): string { return $this->name; }
}
