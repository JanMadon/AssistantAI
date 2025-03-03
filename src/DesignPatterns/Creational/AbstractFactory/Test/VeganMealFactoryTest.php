<?php

namespace App\DesignPatterns\Creational\AbstractFactory\Test;

use App\DesignPatterns\Creational\AbstractFactory\BreakfastInterface;
use App\DesignPatterns\Creational\AbstractFactory\DinnerInterface;
use App\DesignPatterns\Creational\AbstractFactory\VeganBreakfast;
use App\DesignPatterns\Creational\AbstractFactory\VeganDinner;
use App\DesignPatterns\Creational\AbstractFactory\VeganMealFactory;
use PHPUnit\Framework\TestCase;

final class VeganMealFactoryTest extends TestCase
{
    public function testCanCreateVeganBreakfast(): void
    {
        $meal = (new VeganMealFactory())->createBreakfast();

        self::assertInstanceOf(VeganBreakfast::class, $meal);
        self::assertInstanceOf(BreakfastInterface::class, $meal);
    }

    public function testCanCreateVeganDinner(): void
    {
        $meal = (new VeganMealFactory())->createDinner();

        self::assertInstanceOf(VeganDinner::class, $meal);
        self::assertInstanceOf(DinnerInterface::class, $meal);
    }
}