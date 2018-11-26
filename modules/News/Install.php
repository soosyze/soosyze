<?php

namespace News;

class Install
{
    public function install($container)
    {
        $container->query()->insertInto('node_type', [ 'node_type', 'node_type_name',
                'node_type_description' ])
            ->values([
                'node_type'             => 'article',
                'node_type_name'        => 'Article',
                'node_type_description' => 'Utilisez les articles pour vos actualités et des billets de blog.'
            ])
            ->execute();

        $container->query()->insertInto('field', [
                'field_name',
                'field_type',
                'field_rules'
            ])
            ->values([ 'summary', 'textarea', '!required|string|max:255' ])
            ->execute();

        $container->query()->insertInto('node_type_field', [
                'node_type',
                'field_id',
                'field_weight'
            ])
            /* Body */
            ->values([ 'article', 1, 2 ])
            /* Summaray */
            ->values([ 'article', 2, 1 ])
            ->execute();

        $container->query()->insertInto('node', [
                'title',
                'type',
                'created',
                'changed',
                'published',
                'field'
            ])
            ->values([
                'title'     => 'Bienvenue sur mon site',
                'type'      => 'article',
                'created'   => (string) time(),
                'changed'   => (string) time(),
                'published' => true,
                'field'     => serialize([
                    'summary' => 'Un article se met en valeur par un résumé qui décrit brièvement '
                    . 'son contenu avec un nombre de caractères limité (maximum 255 caractères).',
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
    <li>Comme pour les titre, le gras et l\'italique ont un impact pour le reférencement de votre site. 
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
<p>Le reste ne dépend que de vous. Bon courage !</p>'
                ])
            ])
            ->execute();
    }

    public function hookInstall($container)
    {
        $this->hookInstallMenu($container);
    }

    public function hookInstallMenu($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->insertInto('menu_link', [
                    'title_link',
                    'link',
                    'menu',
                    'weight',
                    'parent',
                ])
                ->values([
                    'Blog',
                    'news',
                    'main-menu',
                    3,
                    -1
                ])
                ->execute();
        }
    }

    public function uninstall($container)
    {
        if ($container->schema()->hasTable('menu')) {
            $container->query()->from('menu_link')
                ->delete()
                ->where('link', 'news')
                ->execute();
        }

        $container->query()->from('node')
            ->delete()
            ->where('type', 'article')
            ->execute();

        $container->query()->from('node_type_field')
            ->delete()
            ->where('node_type', 'article')
            ->execute();

        $container->query()->from('node_type')
            ->delete()
            ->where('node_type', 'article')
            ->execute();
    }
}
