<?php
declare(strict_types=1);

namespace App\DesignPatterns\Creational\AbstractFactory;

interface BreakfastInterface
{
    public function shouldAddVitaminB12Supplement(): bool;
}