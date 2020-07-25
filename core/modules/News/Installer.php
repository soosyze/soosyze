<?php

namespace SoosyzeCore\News;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;
use Soosyze\Components\Template\Template;

class Installer extends \SoosyzeCore\System\Migration
{
    protected $pathContent;

    public function __construct()
    {
        $this->pathContent = __DIR__ . '/Views/Content/';
    }

    public function getDir()
    {
        return __DIR__;
    }

    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/lang/fr/config.json');
        $this->loadTranslation('fr', __DIR__ . '/lang/fr/main.json');
    }

    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('entity_article', function (TableBuilder $table) {
                $table->increments('article_id')
                ->string('image')
                ->text('summary')
                ->text('body')
                ->integer('reading_time')->comment('In minute');
            });
        $ci->query()->insertInto('node_type', [
                'node_type',
                'node_type_name',
                'node_type_description',
                'node_type_icon'
            ])
            ->values([
                'node_type'             => 'article',
                'node_type_name'        => 'Article',
                'node_type_description' => 'Use articles for your news and blog posts.',
                'node_type_icon'        => 'fas fa-newspaper'
            ])
            ->execute();

        $idImage   = $ci->query()->from('field')->where('field_name', 'image')->fetch()[ 'field_id' ];
        $idSummary = $ci->query()->from('field')->where('field_name', 'summary')->fetch()[ 'field_id' ];
        $idBody    = $ci->query()->from('field')->where('field_name', 'body')->fetch()[ 'field_id' ];
        $idReading = $ci->query()->from('field')->where('field_name', 'reading_time')->fetch()[ 'field_id' ];

        $ci->query()
            ->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_weight', 'field_label', 'field_rules',
                'field_description', 'field_show_form'
            ])
            ->values([
                'article', $idImage, 1, 'Picture',
                '!required|image|max:800kb',
                'The weight of the image must be less than or equal to 800ko',
                true
            ])
            ->values([
                'article', $idSummary, 2, 'Summary',
                'required|string|max:512',
                'Briefly summarize your article in less than 512 characters',
                true
            ])
            ->values([
                'article', $idBody, 3, 'Body',
                'string',
                '',
                true
            ])
            ->values([
                'article', $idReading, 4, 'Reading time',
                'number|min:1',
                '',
                false
            ])
            ->execute();

        $ci->config()
            ->set('settings.news_pagination', 6)
            ->set('settings.new_default_image', '')
            ->set('settings.new_default_icon', 'fas fa-newspaper')
            ->set('settings.new_default_color', '#fff')
            ->set('settings.node_url_article', 'news/:date_created_year/:date_created_month/:date_created_day/:node_title');
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('entity_article', [ 'image', 'summary', 'body', 'reading_time' ])
            ->values([
                'https://picsum.photos/id/1/650/300',
                '<p>Un article se met en valeur par un résumé qui décrit brièvement '
                . 'son contenu avec un nombre de caractères limité (maximum 255 caractères).</p>',
                (new Template('article_1.php', $this->pathContent))->render(),
                1
            ])
            ->values([
                'https://picsum.photos/id/11/650/300',
                '<p>Consectetur adipiscing elit. Etiam orci nulla, dignissim eu hendrerit ullamcorper, blandit et arcu. '
                . 'Vivamus imperdiet, felis eget suscipit pellentesque, est tortor rutrum tortor.</p>',
                (new Template('article_2.php', $this->pathContent))->render(),
                1
            ])
            ->execute();

        $time = (string) time();
        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'date_created', 'date_changed', 'node_status_id',
                'entity_id'
            ])
            ->values([
                'Bienvenue sur mon site', 'article', $time, $time, 1, 1
            ])
            ->values([
                'Lorem ipsum dolor sit amet', 'article', $time, $time, 1, 2
            ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'menu', 'weight', 'parent', 'active'
                ])
                ->values([ 'news.index', 'Blog', 'news', 'menu-main', 3, -1, false ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->query()->from('node')
            ->delete()
            ->where('type', 'article')
            ->execute();
        $ci->query()->from('node_type_field')
            ->delete()
            ->where('node_type', 'article')
            ->execute();
        $ci->query()->from('node_type')
            ->delete()
            ->where('node_type', 'article')
            ->execute();
        $ci->schema()->dropTable('entity_article');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallBlock($ci);
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallBlock(ContainerInterface $ci)
    {
        if ($ci->module()->has('Block')) {
            $ci->query()
                ->from('block')
                ->delete()
                ->where('hook', 'like', 'news.%')
                ->execute();
        }
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->menu()->deleteLinks(function () use ($ci) {
                return $ci->query()
                        ->from('menu_link')
                        ->where('key', 'like', 'news%')
                        ->fetchAll();
            });
        }
    }

    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'news.%')
                ->execute();
        }
    }
}
