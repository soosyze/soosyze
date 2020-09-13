<?php

namespace SoosyzeCore\Block;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

class Installer extends \SoosyzeCore\System\Migration
{
    public function getDir()
    {
        return __DIR__;
    }
    
    public function boot()
    {
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/main.json');
        $this->loadTranslation('fr', __DIR__ . '/Lang/fr/permission.json');
    }
    
    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('block', function (TableBuilder $table) {
                $table->increments('block_id')
                ->string('title')
                ->string('section')
                ->text('content')->nullable()
                ->string('class')->valueDefault('')
                ->text('hook')->nullable()
                ->integer('weight')
                ->boolean('visibility_pages')->valueDefault(false)
                ->string('pages')->valueDefault('admin/%' . PHP_EOL . 'user/%')
                ->boolean('visibility_roles')->valueDefault(true)
                ->string('roles')->valueDefault('1,2')
                ->string('key_block')->nullable()
                ->string('options')->nullable();
            });

        $ci->config()->set('settings.icon_socials', [
            'blogger'    => '',
            'dribbble'   => '',
            'facebook'   => '#',
            'github'     => '',
            'instagram'  => '#',
            'linkedin'   => '#',
            'mastodon'   => '#',
            'snapchat'   => '',
            'soundcloud' => '',
            'spotify'    => '',
            'steam'      => '',
            'tumblr'     => '',
            'twitch'     => '#',
            'twitter'    => '#',
            'youtube'    => '#'
        ]);
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
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
            ->values([
                'footer', '',
                '<p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>',
                50,
                false, ''
            ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallMenu($ci);
        $this->hookInstallUser($ci);
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'icon', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'block.section.admin', 'fa fa-columns', 'Block', 'admin/section/theme',
                    'menu-admin', 7, -1
                ])
                ->execute();
        }
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'block.administer' ])
                ->values([ 3, 'block.created' ])
                ->values([ 3, 'block.edited' ])
                ->values([ 3, 'block.deleted' ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTableIfExists('block');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->menu()->deleteLinks(function () use ($ci) {
                return $ci->query()
                        ->from('menu_link')
                        ->where('key', 'like', 'block%')
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
                ->where('permission_id', 'like', 'block.%')
                ->execute();
        }
    }
}
