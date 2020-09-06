
<ul>
    <?php foreach ($menu as $link): ?>

    <li class="<?php echo $link[ 'link_active' ]; ?>">
        <a href="<?php echo $link[ 'link' ]; ?>"<?php if ($link[ 'target_link' ]): ?> target="_self" rel="noopener noreferrer" <?php endif; ?>>
            <?php echo $link[ 'title_link' ]; ?></a>
    </li>
    <?php endforeach; ?>
 
</ul>