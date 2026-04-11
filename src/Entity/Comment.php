<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use CafeCulture\BlogBundle\Repository\CommentRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'cafe_comment')]
#[ORM\HasLifecycleCallbacks]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Article $article = null;

    #[ORM\Column(type: Types::STRING, length: 100)]
    #[Assert\NotBlank]
    private string $authorName = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Email]
    #[Assert\NotBlank]
    private string $authorEmail = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 2000)]
    private string $content = '';

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $approved = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getArticle(): ?Article { return $this->article; }
    public function setArticle(?Article $article): static { $this->article = $article; return $this; }

    public function getAuthorName(): string { return $this->authorName; }
    public function setAuthorName(string $name): static { $this->authorName = $name; return $this; }

    public function getAuthorEmail(): string { return $this->authorEmail; }
    public function setAuthorEmail(string $email): static { $this->authorEmail = $email; return $this; }

    public function getContent(): string { return $this->content; }
    public function setContent(string $content): static { $this->content = $content; return $this; }

    public function isApproved(): bool { return $this->approved; }
    public function setApproved(bool $approved): static { $this->approved = $approved; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
