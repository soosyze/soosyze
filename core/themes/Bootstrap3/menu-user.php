
<nav id="nav_user">
    <ul class="nav nav-tabs">
        <?php foreach ($menu as $link): ?>

            <li>
                <a href="<?php echo $link[ 'link' ]; ?>" class="btn btn-action">
                    <?php echo !empty($link['icon']) ? "<i class='{$link['icon']}' aria-hidden='true'></i> {$link[ 'title_link' ]}" : $link[ 'title_link' ]; ?>
                </a>
            </li>
        <?php endforeach; ?>

    </ul>
</nav>