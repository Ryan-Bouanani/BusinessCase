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

    public function stars(?float $note)
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
                str_replace('star', "star-half", $stars[4]);
            } elseif ($note < 4 and $note >= 3.5) {
                $newStar = str_replace("yellow", "", $stars[4]);
                array_splice($stars, 4, 1, $newStar);
            } elseif ($note < 3.5 and $note >= 2.5) {
                $stars = $this->forStars(3, $stars);
    
            } elseif ($note < 2.5 and $note >= 1.5) {
                $stars = $this->forStars(2, $stars);
    
            } elseif ($note < 1.5 and $note >= 0.5) {
                $stars = $this->forStars(1, $stars);
    
            } elseif ($note < 0.5 and $note >= 0) {
                $stars = $this->forStars(0, $stars);
            } 
        } else {
            $stars = $this->forStars(0, $stars);
        }
        $stars =  implode ("", $stars); 
        return $stars;
    }
     public function forStars($keyStars, $stars) {
        for ($i=$keyStars; $i < 5; $i++) { 
            $newStar = str_replace("yellow", "", $stars[$i]);
            array_splice($stars, $i, 1, $newStar);
        }
        return $stars;
     }
}
