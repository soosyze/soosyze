<?php

namespace SoosyzeCore\System\Services;

class Alias
{
    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var \SoosyzeCore\QueryBuilder\Services\Query
     */
    private $query;

    public function __construct($config, $query)
    {
        $this->config = $config;
        $this->query  = $query;
    }

    public function getAlias($source, $default = null)
    {
        $alias = $this->query->from('system_alias_url')->where('source', $source)->fetch();

        return empty($alias)
            ? $default
            : $alias[ 'alias' ];
    }

    public function getSource($alias, $default = null)
    {
        if ($alias === '/') {
            $alias = empty($this->config[ 'settings.path_index' ])
                ? $default
                : $this->config[ 'settings.path_index' ];
        }

        $source = $this->query->from('system_alias_url')->where('alias', $alias)->fetch();

        return empty($source)
            ? $default
            : $source[ 'source' ];
    }
}
