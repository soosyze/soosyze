
<?php echo $node_submenu; ?>

<?php foreach ($fields as $field): ?>
    <?php if ($field[ 'field_show_label' ]): ?>
        <h2><?php echo t($field[ 'field_label' ]); ?></h2>
    <?php endif; ?>
    <?php echo xss($field[ 'field_display' ]); ?>
<?php endforeach; ?>
