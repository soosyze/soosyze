<?php

namespace SoosyzeCore\System\Services;

class Alias
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
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
        $source = $this->query->from('system_alias_url')->where('alias', $alias)->fetch();

        return empty($source)
            ? $default
            : $source[ 'source' ];
    }
}
