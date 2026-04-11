<?php

declare(strict_types=1);

namespace CafeCulture\BlogBundle\Twig;

use CafeCulture\BlogBundle\Repository\ArticleRepository;
use CafeCulture\BlogBundle\Repository\CategoryRepository;
use CafeCulture\BlogBundle\Repository\TagRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;

class CafeCultureExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly ArticleRepository  $articleRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TagRepository      $tagRepository,
    ) {}

    public function getGlobals(): array
    {
        return [
            'cafe_categories'    => $this->categoryRepository->findAllOrdered(),
            'cafe_recent'        => $this->articleRepository->findRecent(5),
            'cafe_popular_tags'  => $this->tagRepository->findPopular(15),
            'cafe_most_viewed'   => $this->articleRepository->findMostViewed(5),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('reading_time', [$this, 'readingTime']),
            new TwigFilter('excerpt', [$this, 'makeExcerpt']),
            new TwigFilter('stars', [$this, 'toStars'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('coffee_flag', [$this, 'coffeeOriginFlag'], ['is_safe' => ['html']]),
            new TwigFunction('aroma_badge', [$this, 'aromaBadge'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Calcule le temps de lecture estimé d'un texte.
     */
    public function readingTime(string $text, int $wpm = 200): string
    {
        $words   = str_word_count(strip_tags($text));
        $minutes = (int) ceil($words / $wpm);
        return $minutes <= 1 ? '1 min de lecture' : "{$minutes} min de lecture";
    }

    /**
     * Génère un extrait de texte avec troncature propre.
     */
    public function makeExcerpt(string $text, int $length = 160): string
    {
        $clean = strip_tags($text);
        if (mb_strlen($clean) <= $length) {
            return $clean;
        }
        $cut = mb_substr($clean, 0, $length);
        $pos = mb_strrpos($cut, ' ');
        return ($pos !== false ? mb_substr($cut, 0, $pos) : $cut) . '…';
    }

    /**
     * Convertit une note numérique en étoiles HTML.
     */
    public function toStars(float $rating, int $max = 5): string
    {
        $full  = (int) floor($rating);
        $half  = ($rating - $full) >= 0.5;
        $empty = $max - $full - ($half ? 1 : 0);
        $html  = '<span class="stars" aria-label="Note : ' . $rating . '/' . $max . '">';
        $html .= str_repeat('<i class="fa fa-star text-amber"></i>', $full);
        if ($half) {
            $html .= '<i class="fa fa-star-half-alt text-amber"></i>';
        }
        $html .= str_repeat('<i class="fa fa-star text-gray-300"></i>', max(0, $empty));
        $html .= '</span>';
        return $html;
    }

    /**
     * Emoji drapeau pour l'origine géographique du café.
     */
    public function coffeeOriginFlag(string $origin): string
    {
        $flags = [
            'Éthiopie'   => '🇪🇹', 'Colombie'  => '🇨🇴', 'Brésil'    => '🇧🇷',
            'Kenya'      => '🇰🇪', 'Guatemala' => '🇬🇹', 'Costa Rica' => '🇨🇷',
            'Pérou'      => '🇵🇪', 'Mexique'   => '🇲🇽', 'Tanzanie'  => '🇹🇿',
            'Indonésie'  => '🇮🇩', 'Vietnam'   => '🇻🇳', 'Jamaïque'  => '🇯🇲',
            'Yémen'      => '🇾🇪', 'Rwanda'    => '🇷🇼', 'Honduras'  => '🇭🇳',
            'Inde'       => '🇮🇳', 'Équateur'  => '🇪🇨', 'Bolivie'   => '🇧🇴',
        ];
        $flag = $flags[$origin] ?? '☕';
        return "<span class=\"coffee-origin-flag\" title=\"{$origin}\">{$flag}</span>";
    }

    /**
     * Badge HTML pour un arôme.
     */
    public function aromaBadge(string $aroma): string
    {
        $colors = [
            'fruité'     => '#E74C3C', 'floral'     => '#9B59B6',
            'chocolaté'  => '#6B3F2A', 'noisette'   => '#8B6914',
            'caramel'    => '#D4A017', 'épicé'      => '#C0392B',
            'terreux'    => '#795548', 'fumé'       => '#607D8B',
            'vanillé'    => '#F8C471', 'agrumes'    => '#F39C12',
        ];
        $color = $colors[strtolower($aroma)] ?? '#6B3F2A';
        return "<span class=\"aroma-badge\" style=\"background-color:{$color}\">{$aroma}</span>";
    }
}
