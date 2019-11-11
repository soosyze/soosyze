
<ul>
    <?php foreach ($archive as $value): ?>
    <li><a href="<?php echo $value['link']; ?>"><?php echo $value['year'] . ' (' . $value['number']; ?>)</a></li>
    <?php endforeach; ?>
</ul>