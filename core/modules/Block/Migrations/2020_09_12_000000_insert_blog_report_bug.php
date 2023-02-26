<?php

use Soosyze\Core\Modules\System\Contract\DatabaseMigrationInterface;
use Soosyze\Queryflatfile\Request;
use Soosyze\Queryflatfile\Schema;

return new class implements DatabaseMigrationInterface {
    public function up(Schema $sch, Request $req): void
    {
        $req
            ->insertInto('block', [
                'section', 'title',
                'weight', 'visibility_pages', 'pages',
                'content'
            ])
            ->values([
                'content_footer', '',
                50, true, 'admin/%',
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
                . '</div>'
            ])
            ->execute();
    }
};
