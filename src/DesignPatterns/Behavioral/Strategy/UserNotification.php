<?php
declare(strict_types=1);

namespace App\DesignPatterns\Behavioral\Strategy;

final class UserNotification
{
    public const PASSWORD_EXPIRE_MESSAGE = 'Password expire';

    public function onPasswordExpired(NotificationPreference $notificationPreference): array
    {
        $messages = [];
        $notifyStrategies = $this->determineStrategies($notificationPreference);
        /** @var NotifyInterface $notifyStrategy */
        foreach ($notifyStrategies as $notifyStrategy) {
            $messages[] = $notifyStrategy->notify(self::PASSWORD_EXPIRE_MESSAGE);
        }

        return $messages;
    }

    private function determineStrategies(NotificationPreference $notificationPreference): array
    {
        $notifyStrategies = [];

        if ($notificationPreference->shouldNotifyByEmail()) {
            $notifyStrategies[] = new EmailNotifier();
        }

        if ($notificationPreference->shouldNotifyBySystem()) {
            $notifyStrategies[] = new SystemNotifier();
        }

        return $notifyStrategies;
    }
}