<?php

namespace SoosyzeCore\QueryBuilder\Services;

class Query extends \Queryflatfile\Request
{
    protected $listRequest = [];

    public function fetchAll()
    {
        $request   = (string) $this;
        $timeStart = microtime(true);
        $output    = parent::fetchAll();

        $this->listRequest[] = [
            'request' => $request,
            'time'    => microtime(true) - $timeStart
        ];

        return $output;
    }

    public function getListeRequest()
    {
        return $this->listRequest;
    }
}
