<?php


namespace App\Support;


class MarkdownReplacements
{
    public function __construct(
        protected array $replacements
    ) { }

    public function all(): array
    {
        return collect($this->replacements)
            ->filter(fn ($value) => is_string($value) && strlen($value) > 0)
            ->all();
    }

    public function extend(array $replacements): static
    {
        $this->replacements = array_merge($this->replacements, $replacements);

        return $this;
    }
}
