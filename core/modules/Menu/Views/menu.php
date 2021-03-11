
<ul class="<?php echo $level === 1 ? 'nav navbar-nav' : 'dropdown-menu'; ?>">
    <?php foreach ($menu as $link): ?>

    <li class="<?php echo empty($link[ 'submenu' ]) ? '' : '​dropdown-submenu'; ?> <?php echo $link[ 'link_active' ]; ?>">
        <?php if (empty($link[ 'submenu' ])): ?>

        <a href="<?php echo $link[ 'link' ]; ?>"<?php if ($link[ 'target_link' ]): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
            <?php echo if_or(empty($link['icon']), '<i class="' . htmlspecialchars($link['icon']) . '" aria-hidden="true"></i> '); ?>
            <?php echo htmlspecialchars($link[ 'title_link' ]); ?>

        </a>
        <?php else: ?>

        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <?php echo if_or(empty($link['icon']), '<i class="' . htmlspecialchars($link['icon']) . '" aria-hidden="true"></i> '); ?>
            <?php echo htmlspecialchars($link[ 'title_link' ]); ?> <span class="caret"></span>

        </a>
        <?php echo $link[ 'submenu' ]; ?>
        <?php endif; ?>

    </li>
    <?php endforeach; ?>

</ul>