
<?php foreach ($fields as $field): ?>
    <?php if ($field[ 'field_show_label' ]): ?>
        <h2><?php echo t($field[ 'field_label' ]); ?></h2>
    <?php endif; ?>

    <?php if ($field[ 'field_type' ] === 'one_to_many'): ?>
        <?php echo $field[ 'field_display' ]; ?>
    <?php else: ?>
        <?php echo xss($field[ 'field_display' ]); ?>
    <?php endif; ?>
<?php endforeach; ?>
