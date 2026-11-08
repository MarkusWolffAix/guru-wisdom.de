<?php

declare(strict_types=1);

namespace App\Service;

use Yiisoft\Translator\TranslatorInterface;

/**
 * Resolves the display label of a wisdom category.
 *
 * The front matter holds a short key (e.g. "history"). The label comes from the message file
 * (resources/messages/<locale>/categories.php, translator category "categories"). A value without a message, such as
 * an old German label or a typo, is returned unchanged, so old and new front matter both keep working.
 */
final class CategoryLabelService
{
    private const MESSAGE_CATEGORY = 'categories';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param string $category The category value from the front matter (quotes are ignored).
     * @param string $locale   The language code ('de' or 'en').
     * @return string The translated label, or the cleaned value if there is no translation.
     */
    public function label(string $category, string $locale): string
    {
        $category = trim($category, " \t\n\r\0\x0B\"'");
        if ($category === '') {
            return '';
        }

        $label = $this->translator->translate($category, [], self::MESSAGE_CATEGORY, $locale);

        return $label === '' ? $category : $label;
    }

    /**
     * Builds a map "cleaned category value => label" for all categories used in the given wisdoms.
     *
     * @param iterable<array<string, mixed>> $wisdoms Entries with an optional 'categories' list.
     * @param string                         $locale  The language code ('de' or 'en').
     * @return array<string, string>
     */
    public function labelsFor(iterable $wisdoms, string $locale): array
    {
        $labels = [];
        foreach ($wisdoms as $wisdom) {
            foreach ((array)($wisdom['categories'] ?? []) as $category) {
                $clean = trim((string)$category, " \t\n\r\0\x0B\"'");
                if ($clean !== '' && !isset($labels[$clean])) {
                    $labels[$clean] = $this->label($clean, $locale);
                }
            }
        }

        return $labels;
    }
}
