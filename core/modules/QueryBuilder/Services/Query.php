<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\QueryBuilder\Services;

class Query extends \Queryflatfile\Request
{
    /**
     * @var array
     */
    private $listRequest = [];

    public function fetchAll(): array
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

    public function getListeRequest(): array
    {
        return $this->listRequest;
    }
}
