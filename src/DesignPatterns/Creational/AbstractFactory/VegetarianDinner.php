<?php

namespace App\DesignPatterns\Creational\AbstractFactory;

final class VegetarianDinner implements DinnerInterface
{
    public function canBePackedInGlassContainer(): bool
    {
       return false;
    }
}