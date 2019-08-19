<?php

namespace SoosyzeCore\News\Services;

class HookUser
{
    public function hookNewShow()
    {
        return [ 'node.show.published', 'node.administer', 'node.show.article' ];
    }
}
