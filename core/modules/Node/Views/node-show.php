<?php foreach ($fields as $field): ?>
    <?php if ($field[ 'field_show_label' ]): ?>
        <h3><?php echo $field[ 'field_label' ]; ?></h3>
    <?php endif; ?>
    <?php echo $field[ 'field_display' ]; ?>
<?php endforeach; ?>
