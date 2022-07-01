<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Node\Model\Field;

final class OneToManyOption implements \JsonSerializable
{
    public const WEIGHT_FIELD = 'weight';

    /** @var int */
    private $count;

    /** @var string */
    private $localKey;

    /** @var string */
    private $foreignKey;

    /** @var string|null */
    private $orderBy;

    /** @var string */
    private $relationTable;

    /** @var int|null */
    private $sort;

    /** @var string */
    private $fieldShow;

    /** @var null|string */
    private $fieldTypeShow;

    private function __construct(
        int $count,
        string $localKey,
        string $foreignKey,
        ?string $orderBy,
        string $relationTable,
        ?int $sort,
        string $fieldShow,
        ?string $fieldTypeShow = null
    ) {
        $this->count         = $count;
        $this->localKey      = $localKey;
        $this->foreignKey    = $foreignKey;
        $this->orderBy       = $orderBy;
        $this->relationTable = $relationTable;
        $this->sort          = $sort;
        $this->fieldShow     = $fieldShow;
        $this->fieldTypeShow = $fieldTypeShow;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function getRelationTable(): string
    {
        return $this->relationTable;
    }

    public function getFieldShow(): string
    {
        return $this->fieldShow;
    }

    public function getFieldTypeShow(): ?string
    {
        return $this->fieldTypeShow;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public static function createFromJson(string $json): self
    {
        return self::createFromArray((array) json_decode($json, true));
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data[ 'count' ],
            $data[ 'local_key' ],
            $data[ 'foreign_key' ],
            $data[ 'order_by' ] ?? null,
            $data[ 'relation_table' ],
            $data[ 'sort' ] ?? null,
            $data[ 'field_show' ],
            $data[ 'field_type_show' ] ?? null
        );
    }

    public function jsonSerialize()
    {
        return [
            'count'           => $this->count,
            'local_key'       => $this->localKey,
            'foreign_key'     => $this->foreignKey,
            'order_by'        => $this->orderBy,
            'relation_table'  => $this->relationTable,
            'sort'            => $this->sort,
            'field_show'      => $this->fieldShow,
            'field_type_show' => $this->fieldTypeShow,
        ];
    }
}
