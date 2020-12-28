
<nav>
    <ul class="nav nav-tabs">
        <?php foreach ($menu as $link): ?>

        <li<?php echo if_or($link[ 'key' ] === $key_route, ' class="active"'); ?>>
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo t($link[ 'title_link' ]); ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>