
<nav id="nav_user">
    <ul>
        <?php foreach ($menu as $link): ?>

            <li>
                <a href="<?php echo $link[ 'link' ]; ?>" class="btn btn-action"><?php echo $link[ 'title_link' ]; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>