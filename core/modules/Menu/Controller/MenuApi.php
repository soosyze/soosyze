<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method \SoosyzeCore\Menu\Services\Menu menu()
 */
class MenuApi extends \Soosyze\Controller
{
    public function show(int $menuId, ServerRequestInterface $req): ResponseInterface
    {
        return $this->json(200, self::menu()->renderMenuSelect($menuId));
    }
}
