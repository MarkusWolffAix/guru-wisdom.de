<?php

declare(strict_types=1);

namespace App\Web\Shared;

/**
 * Shared holder for the language switch data. LocaleMiddleware fills it per request,
 * the layout reads it as $languageSwitch.
 */
final class LanguageSwitchHolder
{
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    /** @param array<string, mixed>|null $data */
    public function set(?array $data): void
    {
        $this->data = $data;
    }

    /** @return array<string, mixed>|null */
    public function get(): ?array
    {
        return $this->data;
    }
}
