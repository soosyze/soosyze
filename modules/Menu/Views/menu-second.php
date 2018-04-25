<ul class="nav navbar-nav navbar-right">
    <?php foreach ($menu as $link): ?>
        <li class="<?php echo $link[ 'link_active' ] ?>">
            <a href="?<?php echo $link[ 'target_link' ] ?>">
                <?php echo $link[ 'title_link' ] ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>