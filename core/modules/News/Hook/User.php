<?php

namespace SoosyzeCore\News\Hook;

class User
{
    public function hookNewShow()
    {
        return [ 'node.show.published', 'node.administer', 'node.show.article' ];
    }
}
