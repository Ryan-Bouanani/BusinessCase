<?php

namespace App\Service;

class PriceTaxInclService
{
    public function calcPriceTaxIncl( float $priceExclVat, float $tva, $promoPercentage = null): float {

        // prix ttc snas promo
        $priceTaxIncl = round($priceExclVat + ($priceExclVat * ($tva / 100)),2);
        if ($promoPercentage !== null) {
            // prix ttc avec promotion
            $priceTaxIncl = round($priceTaxIncl - ($priceTaxIncl * ($promoPercentage / 100)), 2);
        } 
        return $priceTaxIncl;
    }
}