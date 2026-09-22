<?php

declare(strict_types=1);

namespace App\Complete;

final class Complete
{
    public function title(): string
    {
        return __('messages.welcome');
    }
}
