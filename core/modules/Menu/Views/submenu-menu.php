
<nav id="nav_config">
    <ul class="nav nav-pills nav-stacked">
        <?php foreach ($menu as $link): ?>

        <li class="<?php if ($link[ 'name' ] === $id): ?>active<?php endif; ?>">
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo $link[ 'title' ]; ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>