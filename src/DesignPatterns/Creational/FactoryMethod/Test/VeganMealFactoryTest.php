<?php

namespace App\DesignPatterns\Creational\FactoryMethod\Test;


use App\DesignPatterns\Creational\FactoryMethod\MealInterface;
use App\DesignPatterns\Creational\FactoryMethod\VeganMeal;
use App\DesignPatterns\Creational\FactoryMethod\VeganMealFactory;
use PHPUnit\Framework\TestCase;

final class VeganMealFactoryTest extends TestCase
{
    public function testCanCreateVeganMeal(): void
    {
        $meal = (new VeganMealFactory())->createMeal();

        self::assertInstanceOf(MealInterface::class, $meal);
        self::assertInstanceOf(VeganMeal::class, $meal);
    }
}