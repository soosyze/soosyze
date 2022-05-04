<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Model\Field;

final class CheckboxOption implements \JsonSerializable
{
    /** @var array<string> */
    private $options;

    private function __construct(
        array $options = []
    ) {
        $this->options = $options;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param numeric|string $value
     */
    public function addOption(string $label, $value): self
    {
        $this->options[ $value ] = $label;

        return $this;
    }

    public static function createFromJson(string $json): self
    {
        return new self((array) json_decode($json, true));
    }

    public static function create(): self
    {
        return new self();
    }

    public function jsonSerialize()
    {
        return $this->options;
    }
}
