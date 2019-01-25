<?php

namespace QueryBuilder\Services;

class Query extends \Queryflatfile\Request
{
    protected $listRequest = [];

    public function fetchAll()
    {
        $request             = (string) $this;
        $time_start          = microtime(true);
        $output              = parent::fetchAll();
        $time_end            = microtime(true);
        $this->listRequest[] = [
            'request' => $request,
            'time'    => $time_end - $time_start
        ];

        return $output;
    }

    public function getListeRequest()
    {
        return $this->listRequest;
    }
}
