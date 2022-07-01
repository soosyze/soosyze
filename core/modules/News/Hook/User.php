<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\News\Hook;

class User implements \Soosyze\Core\Modules\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions): void
    {
    }

    public function hookNewShow(): array
    {
        return [ 'node.administer', 'node.show.published', 'node.show.published.article' ];
    }
}
