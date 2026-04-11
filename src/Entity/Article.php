<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use CafeCulture\BlogBundle\Repository\ArticleRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'cafe_article')]
#[ORM\HasLifecycleCallbacks]
class Article
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED  = 'archived';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255)]
    private string $title = '';

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    private string $content = '';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $coverImage = null;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => self::STATUS_DRAFT])]
    private string $status = self::STATUS_DRAFT;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $featured = false;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $views = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 3])]
    #[Assert\Range(min: 1, max: 5)]
    private int $readingTimeMinutes = 3;

    // Origine du café (ex: Éthiopie, Colombie, Brésil...)
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $coffeeOrigin = null;

    // Profil aromatique (ex: fruité, chocolaté, floral...)
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $aromaProfile = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'cafe_article_tag')]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Rating::class, cascade: ['persist', 'remove'])]
    private Collection $ratings;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $authorName = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $metaTitle = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    private ?string $metaDescription = null;

    public function __construct()
    {
        $this->tags     = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ratings  = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ──────────────────────────────────────────────
    // Getters & Setters
    // ──────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function getExcerpt(): ?string { return $this->excerpt; }
    public function setExcerpt(?string $excerpt): static { $this->excerpt = $excerpt; return $this; }

    public function getCoverImage(): ?string { return $this->coverImage; }
    public function setCoverImage(?string $coverImage): static { $this->coverImage = $coverImage; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function isPublished(): bool { return $this->status === self::STATUS_PUBLISHED; }
    public function isFeatured(): bool { return $this->featured; }
    public function setFeatured(bool $featured): static { $this->featured = $featured; return $this; }

    public function getViews(): int { return $this->views; }
    public function incrementViews(): static { ++$this->views; return $this; }

    public function getReadingTimeMinutes(): int { return $this->readingTimeMinutes; }
    public function setReadingTimeMinutes(int $min): static { $this->readingTimeMinutes = $min; return $this; }

    public function getCoffeeOrigin(): ?string { return $this->coffeeOrigin; }
    public function setCoffeeOrigin(?string $origin): static { $this->coffeeOrigin = $origin; return $this; }

    public function getAromaProfile(): ?array { return $this->aromaProfile; }
    public function setAromaProfile(?array $profile): static { $this->aromaProfile = $profile; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $date): static { $this->publishedAt = $date; return $this; }

    public function getCategory(): ?Category { return $this->category; }
    public function setCategory(?Category $category): static { $this->category = $category; return $this; }

    public function getTags(): Collection { return $this->tags; }
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }
    public function removeTag(Tag $tag): static { $this->tags->removeElement($tag); return $this; }

    public function getComments(): Collection { return $this->comments; }
    public function getApprovedComments(): Collection
    {
        return $this->comments->filter(fn(Comment $c) => $c->isApproved());
    }

    public function getRatings(): Collection { return $this->ratings; }
    public function getAverageRating(): float
    {
        if ($this->ratings->isEmpty()) return 0.0;
        $total = array_sum($this->ratings->map(fn(Rating $r) => $r->getScore())->toArray());
        return round($total / $this->ratings->count(), 1);
    }

    public function getAuthorName(): ?string { return $this->authorName; }
    public function setAuthorName(?string $name): static { $this->authorName = $name; return $this; }

    public function getMetaTitle(): ?string { return $this->metaTitle; }
    public function setMetaTitle(?string $title): static { $this->metaTitle = $title; return $this; }

    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $desc): static { $this->metaDescription = $desc; return $this; }

    public function publish(): static
    {
        $this->status      = self::STATUS_PUBLISHED;
        $this->publishedAt = new \DateTimeImmutable();
        return $this;
    }
}
