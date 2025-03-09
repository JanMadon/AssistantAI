<?php
declare(strict_types=1);

namespace App\DesignPatterns\Behavioral\Strategy;

final class NotificationPreference
{

    public function __construct(
        private bool $email,
        private bool $system
    ) {}

    public function shouldNotifyByEmail(): bool
    {
        return $this->email;
    }

    public function shouldNotifyBySystem(): bool
    {
        return $this->system;
    }
}