<?php

use Queryflatfile\Request;
use Queryflatfile\Schema;
use Soosyze\Config;

return [
    'up' => function (Schema $sch, Request $req) {
        $search  = 'app/files';
        $replace = 'public/files';

        if ($sch->hasTable('entity_article')) {
            $articles = $req->from('entity_article')->fetchAll();
            /** @phpstan-var array{ article_id: int, body: string, summary: string, image: string } $article */
            foreach ($articles as $article) {
                /** @phpstan-var string $body */
                $body    = str_replace($search, $replace, $article[ 'body' ]);
                /** @phpstan-var string $summary */
                $summary = str_replace($search, $replace, $article[ 'summary' ]);
                /** @phpstan-var string $image */
                $image   = str_replace($search, $replace, $article[ 'image' ]);

                $req
                    ->update('entity_article', [
                        'body'    => $body,
                        'summary' => $summary,
                        'image'   => $image
                    ])
                    ->where('article_id', '=', $article[ 'article_id' ])
                    ->execute();
            }
        }

        $pages = $req->from('entity_page')->fetchAll();
        /** @phpstan-var array{ page_id: int, body: string } $page */
        foreach ($pages as $page) {
            $req
                ->update('entity_page', [
                    'body'    => str_replace($search, $replace, $page[ 'body' ])
                ])
                ->where('page_id', '=', $page[ 'page_id' ])
                ->execute();
        }

        $users = $req->from('user')->fetchAll();
        /** @phpstan-var array{ user_id: int, picture: string } $user */
        foreach ($users as $user) {
            if (empty($user[ 'picture' ])) {
                continue;
            }
            $req
                ->update('user', [
                    'picture' => str_replace($search, $replace, $user[ 'picture' ])
                ])
                ->where('user_id', '=', $user[ 'user_id' ])
                ->execute();
        }

        if ($sch->hasTable('block')) {
            $blocks = $req->from('block')->fetchAll();
            /** @phpstan-var array{ block_id: int, content: string } $block */
            foreach ($blocks as $block) {
                $req
                    ->update('block', [
                        'content' => str_replace($search, $replace, $block[ 'content' ])
                    ])
                    ->where('block_id', '=', $block[ 'block_id' ])
                    ->execute();
            }
        }
    },
    'up_config' => function (Config $config) {
        $search  = 'app/files';
        $replace = 'public/files';

        /** @phpstan-var string $logo */
        $logo     = $config->get('settings.logo', '');
        /** @phpstan-var string $favicon */
        $favicon  = $config->get('settings.favicon', '');
        /** @phpstan-var string $imgaeNew */
        $imgaeNew = $config->get('settings.new_default_image', '');

        $config
            ->set('settings.logo', str_replace($search, $replace, $logo))
            ->set('settings.favicon', str_replace($search, $replace, $favicon))
            ->set('settings.new_default_image', str_replace($search, $replace, $imgaeNew));
    }
];
