
<nav>
    <ul class="nav nav-pills">
        <?php foreach ($menu as $key => $link): ?>

        <li class="<?php if ($key === $key_route): ?>active<?php endif; ?>">
            <a href="<?php echo $link[ 'link' ]; ?>"><?php echo t($link[ 'title_link' ]); ?></a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>