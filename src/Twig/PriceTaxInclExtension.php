<?php

namespace App\Twig;

use App\Service\PriceTaxInclService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class PriceTaxInclExtension extends AbstractExtension
{
    public function __construct(
        private PriceTaxInclService $priceTaxInclService,
    ) { }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('priceTaxIncl', [$this, 'calcPriceTaxIncl']),
        ];
    }

// Calcule le prix ttc et ajouter une promo si il y'en a
    public function calcPriceTaxIncl( float $priceExclVat, float $tva, $promoPercentage = null): float {

        return $this->priceTaxInclService->calcPriceTaxIncl($priceExclVat, $tva, $promoPercentage);
    }
}
