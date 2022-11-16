
<ul>
    <?php foreach ($menu as $link): ?>

    <li class="depth-<?php echo $level; ?> <?php echo $link[ 'link_active' ]; ?> <?php if (!empty($link[ 'submenu' ])): echo 'parent'; endif; ?>">
        <a href="<?php echo $link[ 'link' ]; ?>"<?php if ($link[ 'target_link' ]): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
            <?php echo if_or(!empty($link['icon']), '<i class="' . htmlspecialchars($link['icon'] ?? '') . '" aria-hidden="true"></i> '); ?><?php echo $link[ 'title_link' ]; ?>

        </a>
        <?php if (!empty($link[ 'submenu' ])): ?>
            <?php echo $link[ 'submenu' ]; ?>
        <?php endif; ?>

    </li>
    <?php endforeach; ?>

</ul>