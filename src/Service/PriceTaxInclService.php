<?php

namespace App\Service;

use App\Entity\Product;

class PriceTaxInclService
{
    /**
     * Calcule le prix ht ou ttc et ajouter une promo si il y'en a
     *
     * @param Product $product
     * @param boolean $considerPromotion
     * @param boolean $considerTva
     * @return float
     */
    public function calcPriceTaxIncl(Product $product, $considerPromotion = true, bool $includeTax  = true): float {

        $priceExclVat = $product->getPriceExclVat();
        $tva = $product->getTva();
        $promoPercentage = $considerPromotion && $product->getPromotion() && ($product->getPromotion()->getExpirationDate() > new \DateTime('now')) ? $product->getPromotion()->getPercentage() : null;

        if (!$includeTax) {
            // Prix Hors Taxe
            $price = round($priceExclVat, 2);
        } else {
            // prix ttc sans promo
            $price = round($priceExclVat + ($priceExclVat * ($tva / 100)),2);
        }
        if ($promoPercentage !== null) {
            // prix ttc avec promotion
            $price = round($price - ($price * ($promoPercentage / 100)), 2);
        } 
        return $price;
    }
}