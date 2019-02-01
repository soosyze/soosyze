<ul id="nav_links niv1">
    <li>
        <span id="toogle_menu">Menu</span>
        <ul class="niv2">
            <?php foreach ($menu as $link): ?>
                <li class="<?php echo $link[ 'link_active' ] ?>">
                    <a href="<?php echo $link[ 'link' ] ?>" target="<?php echo $link[ 'target_link' ] ?>" <?php if ($link[ 'target_link' ] === '_blank'): ?> rel="noopener noreferrer" <?php endif; ?>>
                        <?php echo $link[ 'title_link' ]; ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </li>
</ul>