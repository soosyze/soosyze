
<nav>
    <ul class="nav nav-pills">
        <?php foreach ($menu as $key => $link): ?>

        <li<?php echo if_or($key === $key_route, ' class="active"'); ?>>
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo t($link[ 'title_link' ]); ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>