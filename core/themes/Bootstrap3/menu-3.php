
<nav id="nav_user">
    <ul class="nav nav-tabs">
        <?php foreach ($menu as $link): ?>

            <li>
                <a href="<?php echo $link[ 'link' ]; ?>" class="btn btn-action">
                    <?php echo if_or($link['icon'], '<i class="' . htmlspecialchars($link['icon']) . '" aria-hidden="true"></i>'); ?>
                    <?php echo $link[ 'title_link' ]; ?>
                </a>
            </li>
        <?php endforeach; ?>

    </ul>
</nav>