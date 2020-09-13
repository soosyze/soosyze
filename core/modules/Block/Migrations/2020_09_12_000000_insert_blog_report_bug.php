<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;

return [
    'up' => function (Schema $sch, Request $req) {
        $req
            ->insertInto('block', [
                'section', 'title',
                'content',
                'weight',
                'visibility_pages', 'pages'
            ])
            ->values([
                'content_footer', '',
                '<div class="block-report_github">'
                . '<p>'
                . '<a href="https://github.com/soosyze/soosyze/issues" '
                . 'rel="noopener noreferrer" '
                . 'target="_blank" '
                . 'title="' . t('Found an bug? Please report it on GitHub.') . '">'
                . '<i class="fa fa-fw fa-bug" aria-hidden="true"></i> '
                . t('Report a bug.')
                . '</a>'
                . '</p>'
                . '</div>',
                50,
                true, 'admin/%'
            ])
            ->execute();
    }
];
