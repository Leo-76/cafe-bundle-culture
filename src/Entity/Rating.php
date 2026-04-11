<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use CafeCulture\BlogBundle\Repository\RatingRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[ORM\Table(name: 'cafe_rating')]
#[ORM\UniqueConstraint(name: 'unique_article_ip', columns: ['article_id', 'ip_address'])]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 5)]
    private int $score = 5;

    #[ORM\Column(type: Types::STRING, length: 45)]
    private string $ipAddress = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $score): static { $this->score = $score; return $this; }

    public function getIpAddress(): string { return $this->ipAddress; }
    public function setIpAddress(string $ip): static { $this->ipAddress = $ip; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
