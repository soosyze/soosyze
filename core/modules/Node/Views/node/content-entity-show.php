
<?php foreach ($entities as $entity): ?>
    <?php foreach ($entity as $field): ?>
        <?php if ($field[ 'field_show_label' ]): ?>
            <h3><?php echo t($field[ 'field_label' ]); ?></h3>
        <?php endif; ?>
        <?php if ($field[ 'field_type' ] === 'textarea'): ?>
            <?php echo $field[ 'field_display' ]; ?>
        <?php else: ?>
            <?php echo xss($field[ 'field_display' ]); ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endforeach; ?>
