
<ul>
    <?php foreach ($menu as $link): ?>

    <li class="<?php echo $link[ 'link_active' ]; ?> <?php echo if_or($link[ 'submenu' ], 'parent'); ?>">
        <a href="<?php echo $link[ 'link' ]; ?>"<?php echo if_or($link[ 'target_link' ], ' target="_blank" rel="noopener noreferrer"'); ?>>
            <?php echo if_or(!empty($link['icon']), "<i class='{$link['icon']}' aria-hidden='true'></i> ") . $link[ 'title_link' ]; ?>

        </a>
        <?php echo not_empty_or($link[ 'submenu' ]); ?>

    </li>
    <?php endforeach; ?>

</ul>