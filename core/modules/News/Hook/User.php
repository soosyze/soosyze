<?php

namespace SoosyzeCore\News\Hook;

class User implements \SoosyzeCore\User\UserInterface
{
    public function hookUserPermissionModule(array &$permissions)
    {
    }

    public function hookNewShow()
    {
        return [ 'node.administer', 'node.show.published', 'node.show.published.article' ];
    }
}
