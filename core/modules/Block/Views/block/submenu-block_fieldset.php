
<?php if ($menu): ?>

<nav class="pane-block">
    <ul class="nav nav-pills">
        <?php foreach ($menu as $link): ?>

        <li>
            <a href="<?php echo $link[ 'link' ] ?>" class="tab-links <?php echo $link['class']; ?>" data-toogle="tab">
                <?php echo $link[ 'title_link' ]; ?>
            </a>
        </li>
        <?php endforeach; ?>

    </ul>
</nav>
<?php endif; ?>