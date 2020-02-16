<?php

namespace SoosyzeCore\News;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;
use Soosyze\Components\Template\Template;

class Installer implements \SoosyzeCore\System\Migration
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

    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('entity_article', function (TableBuilder $table) {
                $table->increments('article_id')
                ->string('image')
                ->text('summary')
                ->text('body');
            });
        $ci->query()->insertInto('node_type', [
                'node_type', 'node_type_name', 'node_type_description'
            ])
            ->values([
                'node_type'             => 'article',
                'node_type_name'        => 'Article',
                'node_type_description' => 'Use articles for your news and blog posts.'
            ])
            ->execute();

        $idImage   = $ci->query()->from('field')->where('field_name', 'image')->fetch()[ 'field_id' ];
        $idSummary = $ci->query()->from('field')->where('field_name', 'summary')->fetch()[ 'field_id' ];
        $idBody    = $ci->query()->from('field')->where('field_name', 'body')->fetch()[ 'field_id' ];

        $ci->query()
            ->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_weight', 'field_label', 'field_rules'
            ])
            ->values([ 'article', $idImage, 1, 'Picture', 'required|image|max:800kb' ])
            ->values([ 'article', $idSummary, 2, 'Summary', 'required|string|max:512' ])
            ->values([ 'article', $idBody, 3, 'Body', 'string' ])
            ->execute();

        $ci->config()
            ->set('settings.news_pagination', 6);
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('entity_article', [ 'image', 'summary', 'body' ])
            ->values([
                'https://picsum.photos/id/1/650/300',
                '<p>Un article se met en valeur par un résumé qui décrit brièvement '
                . 'son contenu avec un nombre de caractères limité (maximum 255 caractères).</p>',
                (new Template('article_1.php', $this->pathContent))->render()
            ])
            ->values([
                'https://picsum.photos/id/11/650/300',
                '<p>Consectetur adipiscing elit. Etiam orci nulla, dignissim eu hendrerit ullamcorper, blandit et arcu. '
                . 'Vivamus imperdiet, felis eget suscipit pellentesque, est tortor rutrum tortor.</p>',
                (new Template('article_2.php', $this->pathContent))->render()
            ])
            ->execute();

        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'date_created', 'date_changed', 'published', 'entity_id'
            ])
            ->values([
                'Bienvenue sur mon site',
                'article',
                (string) time(),
                (string) time(),
                true,
                1
            ])
            ->values([
                'Lorem ipsum dolor sit amet',
                'article',
                (string) time(),
                (string) time(),
                true,
                2
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
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()->from('menu_link')
                ->delete()
                ->where('link', 'like', 'news%')
                ->execute();
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
