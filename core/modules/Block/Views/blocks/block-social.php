
<ul class="icons">
    <?php foreach ($icon_socials as $key => $icon): ?>
        <?php if ($icon): ?>
            <li>
                <a href="<?php echo $icon; ?>" class="fab fa-<?php echo $key; ?>">
                    <i aria-hidden="true"><?php echo $key; ?></i>
                </a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>