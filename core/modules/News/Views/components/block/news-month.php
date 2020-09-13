
<ul>
<?php foreach ($years as $year): ?>

    <li><a href="<?php echo $year[ 'link' ]; ?>"><?php echo $year[ 'year' ] . ' (' . $year[ 'number' ]; ?>)</a>
    <?php if (!empty($year[ 'months' ])): ?>

        <ul>
        <?php foreach ($year[ 'months' ] as $month): ?>

            <li><a href="<?php echo $month[ 'link' ]; ?>"><?php echo $month[ 'month' ] . ' (' . $month[ 'number' ]; ?>)</a></li>
        <?php endforeach; ?>

        </ul>
    <?php endif; ?>

    </li>
<?php endforeach; ?>
</ul>