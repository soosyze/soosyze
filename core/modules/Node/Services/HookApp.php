<?php

namespace SoosyzeCore\Node\Services;

class HookApp
{
    public function hookResponseAfter($response, $node)
    {
        $robots = '';
        if ($node[ 'meta_noindex' ]) {
            $robots .= 'noindex,';
        }
        if ($node[ 'meta_nofollow' ]) {
            $robots .= 'nofollow,';
        }
        if ($node[ 'meta_noarchive' ]) {
            $robots .= 'noarchive,';
        }
        if ($robots) {
            $response->view('this', [
                'meta' => '<meta name="robots" content="' . substr($robots, 0, -1) . '">' . PHP_EOL
            ]);
        }
        if (!$node[ 'published' ]) {
            $response->view('page.messages', [
                'infos' => [ t('This content is not published') ]
            ]);
        }
    }
}
