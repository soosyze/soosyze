<?php

namespace SoosyzeCore\News;

use Psr\Container\ContainerInterface;

class Installer implements \SoosyzeCore\System\Migration
{
    public function getComposer()
    {
        return __DIR__ . '/composer.json';
    }

    public function install(ContainerInterface $ci)
    {
        $ci->query()->insertInto('node_type', [
                'node_type', 'node_type_name', 'node_type_description'
            ])
            ->values([
                'node_type'             => 'article',
                'node_type_name'        => 'Article',
                'node_type_description' => 'Utilisez les articles pour vos actualités et des billets de blog.'
            ])
            ->execute();

        $idSummary = $ci->query()->from('field')->where('field_name', 'summary')->fetch()[ 'field_id' ];
        $idBody    = $ci->query()->from('field')->where('field_name', 'body')->fetch()[ 'field_id' ];

        $ci->query()
            ->insertInto('node_type_field', [
                'node_type', 'field_id', 'field_weight', 'field_label'
            ])
            ->values([ 'article', $idSummary, 2, 'Résumé' ])
            ->values([ 'article', $idBody, 3, 'Corps' ])
            ->execute();

        $ci->config()
            ->set('settings.news_pagination', 6);
    }

    public function seeders(ContainerInterface $ci)
    {
        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                'Bienvenue sur mon site',
                'article',
                (string) time(),
                (string) time(),
                true,
                serialize([
                    'summary' => '<img src="https://picsum.photos/id/1/650/300" alt="Illustration">'
                    . '<p>Un article se met en valeur par un résumé qui décrit brièvement '
                    . 'son contenu avec un nombre de caractères limité (maximum 255 caractères).</p>',
                    'body'    => '<p>Quelques conseils pour l\'écriture de vos articles</p>
<ul>
    <li>L\'article peut commencer par son résumé.</li>
    <li>Le résumé ne devrait pas contenir de mise en forme, juste du texte.</li>
    <li>Le titre principal de votre article (titre 1) doit-être unique pour chaque page. 
        Cela aide les moteurs de recherche pour indexer votre site. Le titre 1 est géré par le CMS dans un champ `titre du contenu`.
    </li>
    <li>Les titres doivent suivre une logique : titre 2 > titre 3 > titre 4</li>
    <li>Il est déconseillé de dépasser les 3 niveaux de titre au risque de perdre votre lecteur.</li>
    <li>N\'utiliser pas le gras ou l\'italique juste pour mettre rendre jolie votre article.</li>
    <li>Comme pour les titre, le gras et l\'italique ont un impact pour le référencement de votre site. 
    Ces balises doivent être utilisées pour faire ressortir les informations importantes de votre contenu.
    </li>
    <li>Trop de mise en forme rends la lecture difficile.</li>
    <li>Penser à découper votre pensée en plusieurs paragraphes.</li>
    <li>Justifier votre texte réduit la capacité de vos yeux à retourner à la ligne.</li>
    <li>Vos phrases commencent par une majuscule et finissent par un point.</li>
    <li>Éviter l\'utilisation caractère `:`.</li>
    <li>N\'utiliser pas abusivement les listes à puces. Tout comme les titre éviter de dépasser 3 niveaux.</li>
</ul>

<p>Il ne s\'agit pas de règles immuables, juste des conseils pour l\'écriture de vos articles.</p>
<p>Pensez bien à rester sobre, l\'internaute vient avant tout lire le contenu de vos articles.</p>
<p>Le reste ne dépend que de vous. Bon courage !</p>',
                    'image'   => ''
                ])
            ])
            ->execute();
        $ci->query()
            ->insertInto('node', [
                'title', 'type', 'created', 'changed', 'published', 'field'
            ])
            ->values([
                'Lorem ipsum dolor sit amet',
                'article',
                (string) time(),
                (string) time(),
                true,
                serialize([
                    'summary' => '<img src="https://picsum.photos/id/11/650/300" alt="Illustration">'
                    . '<p>Consectetur adipiscing elit. Etiam orci nulla, dignissim eu hendrerit ullamcorper, blandit et arcu. Vivamus imperdiet, felis eget suscipit pellentesque, est tortor rutrum tortor.</p>',
                    'body'    => '<p>Nam pellentesque ac tellus non porttitor. Pellentesque auctor, dui posuere pellentesque pellentesque, diam purus fringilla arcu, dapibus fermentum orci odio faucibus quam. Proin non neque eros. Mauris vehicula lacus eget fermentum dapibus. Proin luctus orci at sem pellentesque maximus. Cras augue magna, posuere eget euismod at, porta at dolor. Maecenas at mi nec ligula hendrerit malesuada at a diam. Nulla laoreet tincidunt faucibus. Nulla nisl nulla, vehicula sit amet ipsum quis, faucibus aliquam odio. </p>
<p>Suspendisse nec egestas sapien. Vivamus eros sapien, porta id imperdiet vel, venenatis eu sapien. Nulla non massa nec nunc luctus finibus sit amet vitae nibh. Duis vitae venenatis ante. Mauris maximus ligula sed varius cursus. Aliquam vel ex ipsum. Donec interdum aliquam sapien et cursus. Quisque vel dapibus orci, eu tempor nunc. </p>
<p>Praesent ut felis pellentesque, tincidunt diam ac, congue diam. Aliquam erat volutpat. Aenean consequat lobortis eros in posuere. Nullam in enim ut leo euismod posuere quis eu magna.</p>',
                    'image'   => ''
                ])
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
