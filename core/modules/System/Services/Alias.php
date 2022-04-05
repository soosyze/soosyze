<?php

declare(strict_types=1);

namespace SoosyzeCore\System\Services;

use Soosyze\Config;
use SoosyzeCore\QueryBuilder\Services\Query;

/**
 * @phpstan-import-type AliasEntity from \SoosyzeCore\System\Extend
 */
class Alias
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Query
     */
    private $query;

    public function __construct(Config $config, Query $query)
    {
        $this->config = $config;
        $this->query  = $query;
    }

    public function getAlias(string $source, ?string $default = null): ?string
    {
        /** @phpstan-var AliasEntity|null $alias */
        $alias = $this->query->from('system_alias_url')->where('source', '=', $source)->fetch();

        return $alias['alias'] ?? $default;
    }

    public function getSource(string $alias, ?string $default = null): ?string
    {
        if ($alias === '/') {
            /** @phpstan-var string $index */
            $index = $this->config[ 'settings.path_index' ];
            $alias = empty($index)
                ? $alias
                : $index;

            $default = $index;
        }

        /** @phpstan-var AliasEntity|null $source */
        $source = $this->query->from('system_alias_url')->where('alias', '=', ltrim($alias, '/'))->fetch();

        return $source[ 'source' ] ?? $default;
    }
}
