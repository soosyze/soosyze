
<ul>
    <?php foreach ($menu as $link): ?>

    <li class="<?php echo $link[ 'link_active' ]; ?> <?php if (!empty($link[ 'submenu' ])): echo 'parent'; endif; ?>">
        <a href="<?php echo $link[ 'link' ]; ?>"<?php if ($link[ 'target_link' ]): ?> target="_self" rel="noopener noreferrer" <?php endif; ?>>
            <?php echo !empty($link['icon']) ? "<i class='{$link['icon']}' aria-hidden='true'></i> " : ''; ?><?php echo $link[ 'title_link' ]; ?>

        </a>
        <?php if (!empty($link[ 'submenu' ])): ?>
            <?php echo $link[ 'submenu' ]; ?>
        <?php endif; ?>

    </li>
    <?php endforeach; ?>

</ul>