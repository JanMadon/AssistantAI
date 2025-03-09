<?php
declare(strict_types=1);

namespace App\DesignPatterns\Behavioral\Strategy;

interface NotifyInterface
{
    public function notify(string $message): string;
}