<?php

namespace SoosyzeCore\Node\Services;

/**
 * Description of HookApp
 *
 * @author mnoel
 */
class HookApp
{

    public function hookResponseAfter( &$tpl, $node )
    {
        $robots = '';
        if( $node[ 'noindex' ] )
        {
            $robots .= 'noindex,';
        }
        if( $node[ 'nofollow' ] )
        {
            $robots .= 'nofollow,';
        }
        if( $node[ 'noarchive' ] )
        {
            $robots .= 'noarchive,';
        }
        if( $robots )
        {
            $tpl->view('this', [
                'meta' => '<meta name="robots" content="' .  substr($robots, 0, -1) . '">' . PHP_EOL
            ]);
        }
    }
}