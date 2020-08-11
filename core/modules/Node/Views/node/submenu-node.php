
<?php if ($menu): ?>

<nav>
    <ul class="nav nav-tabs">
        <?php foreach ($menu as $link): ?>

        <li <?php if ($link[ 'key' ] === $key_route): ?>class="active"<?php endif; ?>>
            <a href="<?php echo $link[ 'link' ] ?>"><?php echo $link[ 'title_link' ]; ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>
<?php endif; ?>