<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\AbstractFactory;

final class VegetarianBreakfast implements BreakfastInterface
{
    public function shouldAddVitaminB12Supplement(): false
    {
        return false;
    }
}