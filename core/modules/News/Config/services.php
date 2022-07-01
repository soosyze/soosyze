<?php

return [
    'news.extend' => [
        'class' => 'Soosyze\Core\Modules\News\Extend',
        'hooks' => [
            'install.menu' => 'hookInstallMenu',
            'install.user' => 'hookInstallUser'
        ]
    ],
    'news.hook.api.route' => [
        'class' => 'Soosyze\Core\Modules\News\Hook\ApiRoute',
        'arguments' => [
            'newTitle' => '#settings.new_title'
        ],
        'hooks' => [
            'api.route' => 'apiRoute'
        ]
    ],
    'news.hook.user' => [
        'class' => 'Soosyze\Core\Modules\News\Hook\User',
        'hooks' => [
            'route.news.index' => 'hookNewShow',
            'route.news.page' => 'hookNewShow',
            'route.news.years' => 'hookNewShow',
            'route.news.years.page' => 'hookNewShow',
            'route.news.month' => 'hookNewShow',
            'route.news.month.page' => 'hookNewShow',
            'route.news.day' => 'hookNewShow',
            'route.news.day.page' => 'hookNewShow',
            'route.news.rss' => 'hookNewShow'
        ]
    ],
    'news.hook.config' => [
        'class' => 'Soosyze\Core\Modules\News\Hook\Config',
        'hooks' => [
            'config.edit.menu' => 'menu'
        ]
    ],
    'news.hook.block' => [
        'class' => 'Soosyze\Core\Modules\News\Hook\Block',
        'hooks' => [
            'block.create.form.data' => 'hookBlockCreateFormData',
            'block.news.archive.select' => 'hookNewsArchiveSelect',
            'block.news.archive' => 'hookNewsArchive',
            'block.news.archive.create.form' => 'hookNewsArchiveForm',
            'block.news.archive.store.validator' => 'hookNewsArchiveValidator',
            'block.news.archive.store.before' => 'hookNewsArchiveBefore',
            'block.news.archive.edit.form' => 'hookNewsArchiveForm',
            'block.news.archive.update.validator' => 'hookNewsArchiveValidator',
            'block.news.archive.update.before' => 'hookNewsArchiveBefore',
            'block.news.last' => 'hookNewsLast',
            'block.news.last.create.form' => 'hookNewsLastForm',
            'block.news.last.store.validator' => 'hookNewsLastValidator',
            'block.news.last.store.before' => 'hookNewsLastBefore',
            'block.news.last.edit.form' => 'hookNewsLastForm',
            'block.news.last.update.validator' => 'hookNewsLastValidator',
            'block.news.last.update.before' => 'hookNewsLastBefore'
        ]
    ],
    'news.hook.node' => [
        'class' => 'Soosyze\Core\Modules\News\Hook\Node',
        'arguments' => [
            'newDefaultImage' => '#settings.new_default_image',
            'newDefaultIcon' => '#settings.new_default_icon'
        ],
        'hooks' => [
            'node.makefields' => 'hookNodeMakefields',
            'node.create.form.data' => 'hookNodeFormData',
            'node.edit.form.data' => 'hookNodeFormData',
            'node.entity.store.before' => 'hookNodeStoreBefore',
            'node.entity.update.before' => 'hookNodeUpdateBefore',
            'node.show.tpl' => 'hookNodeShowTpl'
        ]
    ]
];
