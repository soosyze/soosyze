
<?php echo $node_submenu; ?>

<?php foreach ($fields as $field): ?>
    <?php if ($field[ 'field_show_label' ]): ?>
        <h2><?php echo $field[ 'field_label' ]; ?></h2>
    <?php endif; ?>
    <?php echo $field[ 'field_display' ]; ?>
<?php endforeach; ?>
