<?php

namespace SoosyzeCore\System;

interface ApiRouteInterface
{
    /**
     * @param array  $routes  Le tableau des routes.
     * @param string $search  Le nom de la route recherché.
     * @param string $exclude Le nom de la route à exclure.
     * @param int    $limit   Le nombre maximum de routes.
     */
    public function apiRoute(array &$routes, $search, $exclude, $limit);
}
