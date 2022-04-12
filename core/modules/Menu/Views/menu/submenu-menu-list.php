
<nav>
    <ul class="nav nav-pills">
        <?php foreach ($menu as $link): ?>

        <li<?php echo if_or($link[ 'menu_id' ] === $key_route, ' class="active"'); ?>>
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo t($link[ 'title' ]); ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>