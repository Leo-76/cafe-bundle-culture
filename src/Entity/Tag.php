<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use CafeCulture\BlogBundle\Repository\TagRepository;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'cafe_tag')]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 80)]
    private string $name = '';

    #[ORM\Column(type: Types::STRING, length: 80, unique: true)]
    private string $slug = '';

    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'tags')]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): static { $this->name = $name; return $this; }

    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }

    public function getArticles(): Collection { return $this->articles; }

    public function __toString(): string { return $this->name; }
}
