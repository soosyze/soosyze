<?php

namespace SoosyzeCore\News\Services;

class HookUser
{
    public function hookRouteNodeSow()
    {
        return [ 'node.show.published', 'node.administer', 'node.show.article'];
    }
}
