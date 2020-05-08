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
        $timeEnd   = microtime(true);

        $this->listRequest[] = [
            'request' => $request,
            'time'    => $timeEnd - $timeStart
        ];

        return $output;
    }

    public function getListeRequest()
    {
        return $this->listRequest;
    }
}
