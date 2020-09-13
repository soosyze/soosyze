
<ul class="<?php echo $level === 1 ? '' : ''; ?>">
    <?php foreach ($menu as $link): ?>

    <li class="<?php echo empty($link[ 'submenu' ]) ? '' : '​'; ?> <?php echo $link[ 'link_active' ]; ?> ">
        <?php if (empty($link[ 'submenu' ])): ?>

        <a href="<?php echo $link[ 'link' ]; ?>" <?php if ($link[ 'target_link' ] === '_blank'): ?>target="<?php echo $link[ 'target_link' ]; ?>" rel="noopener noreferrer" <?php endif; ?>>
            <?php echo !empty($link['icon']) ? "<i class='{$link['icon']}' aria-hidden='true'></i> " : ''; ?><?php echo $link[ 'title_link' ]; ?>
            
        </a>
        <?php else: ?>

        <a href="<?php echo $link[ 'link' ]; ?>">
            <?php echo !empty($link['icon']) ? "<i class='{$link['icon']}' aria-hidden='true'></i> " : ''; ?><?php echo $link[ 'title_link' ]; ?> <span class="caret"></span>
            
        </a>
        <?php echo $link[ 'submenu' ]; ?>
        <?php endif; ?>

    </li>
    <?php endforeach; ?>

</ul>