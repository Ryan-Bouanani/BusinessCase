<?php

namespace App\Enum;

Class StatusEnum
{
    const EXPEDIER = 'Expédiée';
    const ACCEPTER = 'Acceptée';
    const PREPARATION = 'En cours de preparation';
    const ANNULER = 'Annulée';

    public static function getStatus()
    {
        return [
            self::EXPEDIER,
            self::ACCEPTER,
            self::PREPARATION,
            self::ANNULER,
        ];
    }
}