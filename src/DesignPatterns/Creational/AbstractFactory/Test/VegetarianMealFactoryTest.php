<?php

namespace App\DesignPatterns\Creational\AbstractFactory\Test;

use App\DesignPatterns\Creational\AbstractFactory\BreakfastInterface;
use App\DesignPatterns\Creational\AbstractFactory\DinnerInterface;
use App\DesignPatterns\Creational\AbstractFactory\VegetarianBreakfast;
use App\DesignPatterns\Creational\AbstractFactory\VegetarianDinner;
use App\DesignPatterns\Creational\AbstractFactory\VegetarianMealFactory;
use PHPUnit\Framework\TestCase;

final class VegetarianMealFactoryTest extends TestCase
{
    public function testCanCreateVegetarianBreakfast(): void
    {
        $meal = (new VegetarianMealFactory())->createBreakfast();

        self::assertInstanceOf(VegetarianBreakfast::class, $meal);
        self::assertInstanceOf(BreakfastInterface::class, $meal);
    }

    public function testCanCreateVegetarianDinner(): void
    {
        $meal = (new VegetarianMealFactory())->createDinner();

        self::assertInstanceOf(VegetarianDinner::class, $meal);
        self::assertInstanceOf(DinnerInterface::class, $meal);
    }
}