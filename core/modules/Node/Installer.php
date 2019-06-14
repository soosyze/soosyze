<?php

namespace SoosyzeCore\Node;

use Psr\Container\ContainerInterface;
use Queryflatfile\TableBuilder;

class Installer implements \SoosyzeCore\System\Migration
{
    public function getComposer()
    {
        return __DIR__ . '/composer.json';
    }

    public function install(ContainerInterface $ci)
    {
        $ci->schema()
            ->createTableIfNotExists('node', function (TableBuilder $table) {
                $table->increments('id')
                ->string('title')
                ->string('type')
                ->string('created')
                ->string('changed')
                ->boolean('published')
                ->text('field');
            })
            ->createTableIfNotExists('node_type', function (TableBuilder $table) {
                $table->string('node_type')
                ->string('node_type_name')
                ->text('node_type_description');
            })
            ->createTableIfNotExists('field', function (TableBuilder $table) {
                $table->increments('field_id')
                ->string('field_name')
                ->string('field_type')
                ->string('field_rules');
            })
            ->createTableIfNotExists('node_type_field', function (TableBuilder $table) {
                $table->string('node_type')
                ->integer('field_id')
                ->string('field_label')
                ->text('field_description')->valueDefault('')
                ->text('field_default_value')->nullable()
                ->integer('field_weight')->valueDefault(1);
            });

        $ci->query()->insertInto('node_type', [
                'node_type', 'node_type_name', 'node_type_description'
            ])
            ->values([ 'page', 'Page', 'Utilisez les pages pour votre contenu statique.' ])
            ->execute();

        $ci->query()->insertInto('field', [
                'field_name', 'field_type', 'field_rules'
            ])
            ->values([ 'body', 'textarea', 'required|string' ])
            ->values([ 'summary', 'textarea', '!required|string|max:255' ])
            ->execute();

        $ci->query()
            ->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_label'
            ])
            ->values([ 'page', 1, 'Corps' ])
            ->execute();
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                'Page 404', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => 'Page Not Found, Sorry, but the page you were trying to view does not exist.',
                ])
            ])
            ->values([
                'Accueil', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => '<p>Bienvenue sur votre site <a href="?q=user/login">Connexion utilisateur</a></p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam suscipit orci a purus accumsan posuere. 
Nulla gravida risus quis elementum semper. Phasellus id molestie nulla, non viverra diam. Quisque non varius metus. 
In pretium dui id lectus iaculis tempus. Donec ut vestibulum neque. Praesent sed viverra ante, non maximus urna. 
Integer sed arcu semper, dictum elit in, hendrerit nisl. Aliquam tempus nibh diam, sed laoreet nulla fermentum nec.</p>
<h3>Suspendisse auctor ullamcorper molestie.</h3>
<p>Pellentesque turpis nisi, tempus eget facilisis a, imperdiet quis lorem. 
Nunc semper libero libero, nec porttitor tellus ultrices ut. 
Sed tristique facilisis turpis, vitae fringilla dolor suscipit non. 
Cras vitae magna mauris. Quisque malesuada fermentum lectus, ac consectetur diam scelerisque quis. 
Sed vitae finibus turpis.</p>
<h3>Morbi eu viverra turpis. </h3>
<p>Ut nunc odio, pulvinar id placerat eu, varius a diam. In nec nunc eu orci aliquam sodales. 
Sed auctor consequat sem in rhoncus. Morbi quis lorem non elit aliquam laoreet. 
Donec gravida turpis sit amet libero aliquam, eu luctus odio feugiat. 
Nam id mi scelerisque ligula placerat sollicitudin a in lectus. 
Sed vulputate augue eget risus pharetra porttitor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
Suspendisse maximus orci at consequat dictum. Vivamus dictum tellus ut elementum vehicula. 
Suspendisse vitae diam ac lacus convallis varius. 
Proin laoreet congue nunc, tempus interdum massa dapibus ut. In et enim purus.</p>',
                ])
            ])
            ->values([
                'Page', 'page', (string) time(), (string) time(), true,
                serialize([
                    'body' => '<h2>Text</h2>
<p>This is <strong>bold</strong> and this is <strong>strong</strong>. This is <em>italic</em> and this is <em>emphasized</em>.
    This is <sup>superscript</sup> text and this is <sub>subscript</sub> text.
    This is <u>underlined</u> and this is code: <code>for (;;) { ... }</code>.
    Finally, this is a <a href=\'https://soosyze.com\'>link</a>.
</p>
<hr>
<h2>Heading Level 2</h2>
<h3>Heading Level 3</h3>
<h4>Heading Level 4</h4>
<h5>Heading Level 5</h5>
<h6>Heading Level 6</h6>
<hr>
<header>
    <h2>Heading with a Subtitle</h2>
    <p>Lorem ipsum dolor sit amet nullam id egestas urna aliquam</p>
</header>
<p>Nunc lacinia ante nunc ac lobortis. Interdum adipiscing 
    gravida odio porttitor sem non mi integer non faucibus ornare mi ut ante
    amet placerat aliquet. Volutpat eu sed ante lacinia sapien lorem 
    accumsan varius montes viverra nibh in adipiscing blandit tempus 
    accumsan.</p>
<header>
    <h3>Heading with a Subtitle</h3>
    <p>Lorem ipsum dolor sit amet nullam id egestas urna aliquam</p>
</header>
<p>Nunc lacinia ante nunc ac lobortis. Interdum adipiscing 
    gravida odio porttitor sem non mi integer non faucibus ornare mi ut ante
    amet placerat aliquet. Volutpat eu sed ante lacinia sapien lorem 
    accumsan varius montes viverra nibh in adipiscing blandit tempus 
    accumsan.</p>
<header>
    <h4>Heading with a Subtitle</h4>
    <p>Lorem ipsum dolor sit amet nullam id egestas urna aliquam</p>
</header>
<p>Nunc lacinia ante nunc ac lobortis. Interdum adipiscing 
    gravida odio porttitor sem non mi integer non faucibus ornare mi ut ante
    amet placerat aliquet. Volutpat eu sed ante lacinia sapien lorem 
    accumsan varius montes viverra nibh in adipiscing blandit tempus 
    accumsan.</p>
<hr>
<h2>Lists</h2>
<h3>Unordered</h3>
<ul><li>Dolor pulvinar etiam.</li><li>Sagittis lorem eleifend.</li><li>Felis feugiat dolore viverra.</li><li>Dolor pulvinar etiam.</li></ul>
<h3>Ordered</h3>
<ol><li>Dolor pulvinar etiam.</li><li>Etiam vel felis at viverra.</li><li>Felis enim feugiat magna.</li><li>Etiam vel felis nullam.</li><li>Felis enim et tempus.</li></ol>'
                ])
            ])
            ->execute();
    }

    public function hookInstall(ContainerInterface $ci)
    {
        $this->hookInstallUser($ci);
        $this->hookInstallMenu($ci);
    }

    public function hookInstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->insertInto('role_permission', [ 'role_id', 'permission_id' ])
                ->values([ 3, 'node.show.not_published' ])
                ->values([ 3, 'node.show.published' ])
                ->values([ 3, 'node.administer' ])
                ->values([ 3, 'node.index' ])
                ->values([ 2, 'node.show.published' ])
                ->values([ 1, 'node.show.published' ])
                ->execute();
        }
    }

    public function hookInstallMenu(ContainerInterface $ci)
    {
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'menu', 'weight', 'parent'
                ])
                ->values([
                    'node.index',
                    '<i class="fa fa-file" aria-hidden="true"></i> Contenu',
                    'admin/node',
                    'admin-menu',
                    2,
                    -1
                ])
                ->values([
                    'node.show', 'Page', 'node/3', 'main-menu', 2, -1
                ])
                ->values([ 'node.show', 'Accueil', '/', 'main-menu', 1, -1 ])
                ->execute();

            $ci->schema()
                ->createTableIfNotExists('node_menu_link', function (TableBuilder $table) {
                    $table->integer('node_id')
                    ->integer('menu_link_id');
                });

            $menuAccueil = $ci->query()
                ->from('menu_link')
                ->where('key', '==', 'node.show')
                ->where('title_link', '=', 'Accueil')
                ->fetch();

            $menuPage = $ci->query()
                ->from('menu_link')
                ->where('key', '==', 'node.show')
                ->where('title_link', '=', 'Page')
                ->fetch();

            $ci->query()
                ->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                ->values([ 2, $menuAccueil['id'] ])
                ->values([ 3, $menuPage['id'] ])
                ->execute();
        }
    }

    public function uninstall(ContainerInterface $ci)
    {
        $ci->schema()->dropTable('node_type_field');
        $ci->schema()->dropTable('field');
        $ci->schema()->dropTable('node_type');
        $ci->schema()->dropTable('node');
    }

    public function hookUninstall(ContainerInterface $ci)
    {
        $this->hookUninstallMenu($ci);
        $this->hookUninstallUser($ci);
    }

    public function hookUninstallMenu(ContainerInterface $ci)
    {
        if ($ci->schema()->hasTable('node_menu_link')) {
            $ci->schema()->dropTable('node_menu_link');
        }
        if ($ci->module()->has('Menu')) {
            $ci->query()
                ->from('menu_link')
                ->delete()
                ->where('link', 'admin/node')
                ->orWhere('link', 'like', 'node%')
                ->execute();
        }
    }
    
    public function hookUninstallUser(ContainerInterface $ci)
    {
        if ($ci->module()->has('User')) {
            $ci->query()
                ->from('role_permission')
                ->delete()
                ->where('permission_id', 'like', 'node%')
                ->execute();
        }
    }
}
