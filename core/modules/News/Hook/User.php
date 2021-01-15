<?php

namespace SoosyzeCore\News\Hook;

class User
{
    public function hookNewShow()
    {
        return [ 'node.administer', 'node.show.published', 'node.show.published.article' ];
    }
}
