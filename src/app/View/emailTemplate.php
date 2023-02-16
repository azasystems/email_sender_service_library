<?php

declare(strict_types=1);

// Шаблон почты для письма

namespace AzaSystems\App\View;

function getEmailTemplate(string $userName, int $subscriptionId): array
{
    return
        [
            "your subscription #$subscriptionId",
            "$userName, your subscription is expiring soon"
        ];
}
