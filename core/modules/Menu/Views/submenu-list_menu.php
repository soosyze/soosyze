
<nav>
    <ul class="nav nav-pills">
        <?php foreach ($menu as $link): ?>

        <li class="<?php if ($link[ 'name' ] === $key_route): ?>active<?php endif; ?>">
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo t($link[ 'title' ]); ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>