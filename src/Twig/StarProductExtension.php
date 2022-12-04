<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StarProductExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('stars', [$this, 'stars'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Cette fonction va permettre de formatter l'affichage d'une note sous forme d'étoiles
     *
     * @param float $note
     * @return string
     */
    public function stars(float $note): string
    {
        $stars = [
            '<i class=" yellow fa-solid fa-star"></i>',
            '<i class=" yellow fa-solid fa-star"></i>',
            '<i class=" yellow fa-solid fa-star"></i>',
            '<i class=" yellow fa-solid fa-star"></i>',
            '<i class=" yellow fa-solid fa-star"></i>'
        ];

        if ($note) {
            if ($note < 4.5 and $note >= 3.5) {
                $stars = $this->forStars(4, $stars);
            } elseif ($note < 3.5 and $note >= 2.5) {
                $stars = $this->forStars(3, $stars);
    
            } elseif ($note < 2.5 and $note >= 1.5) {
                $stars = $this->forStars(2, $stars);
    
            } elseif ($note < 1.5 and $note >= 0.5) {
                $stars = $this->forStars(1, $stars);
    
            } elseif ($note < 0.5 and $note >= 0) {
                $stars = $this->forStars(0, $stars);
            }
            // On transform le tableau en string pour pouvoir l'afficher
            $stars = implode("", $stars); 
            return $stars;
        }
    }
    // Pour chaque étoile qui ne doit pas être colorée on retire la classe yellow
    public function forStars($keyStars, $stars) {
        for ($i=$keyStars; $i < 5; $i++) { 
            $stars[$i] = str_replace("yellow", "", $stars[$i]);
        }
        return $stars;
    }
}
