<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Model\Field;

/**
 * @phpstan-type Option array{ label: string, value: numeric|string, attr: array}
 */
final class SelectOption implements \JsonSerializable
{
    /** @var array<array{ label: string, value: numeric|string|Option[], attr: array}> */
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
    public function addOption(string $label, $value, array $attr = []): self
    {
        $this->options[] = [ 'label' => $label, 'value' => $value, 'attr' => $attr ];

        return $this;
    }

    public function addOptionGroup(
        string $label,
        \Closure $closure,
        array $attr = []
    ): self {
        $selectOption = new SelectOption();
        $closure($selectOption);

        $this->options[] = [
            'label' => $label,
            'value' => $selectOption->getOptions(),
            'attr'  => $attr
        ];

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
