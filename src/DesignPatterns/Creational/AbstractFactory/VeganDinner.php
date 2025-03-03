<?php

namespace App\DesignPatterns\Creational\AbstractFactory;

final class VeganDinner implements DinnerInterface
{
    public function canBePackedInGlassContainer(): bool
    {
       return true;
    }
}