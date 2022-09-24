<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PriceTaxInclExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('priceTaxIncl', [$this, 'calcPriceTaxIncl']),
        ];
    }


    public function calcPriceTaxIncl(float $priceExclVat, float $tva) {
        $priceTaxIncl = round($priceExclVat * ($tva / 100),2);

        dump($tva);
        dump($priceExclVat);
        // dump($tva);
        return $priceTaxIncl;
    }
}
