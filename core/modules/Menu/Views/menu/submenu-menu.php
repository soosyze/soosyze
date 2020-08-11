
<nav>
    <ul class="nav nav-tabs">
        <?php foreach ($menu as $link): ?>

        <li class="<?php if ($link['key'] === $key_route): ?>active<?php endif; ?>">
            <a href="<?php echo $link[ 'link' ] ?>"><?php echo $link[ 'title_link' ]; ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>