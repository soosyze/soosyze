<?php

/**
 * @see https://mwop.net/blog/2014-08-11-testing-output-generating-code.html
 */

namespace Soosyze\Core\Modules\System\Controller
{
    function session_destroy()
    {
        $_SESSION = [];
    }
}
