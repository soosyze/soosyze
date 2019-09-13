
<fieldset>
    <legend><?php echo t('Type of content'); ?></legend>
    <?php foreach ($node_type as $node): ?>

    <h3><a href="<?php echo $node[ 'link' ]; ?>"><?php echo t($node[ 'node_type_name' ]); ?></a></h3>
    <p><?php echo t($node[ 'node_type_description' ]); ?></p>
    <?php endforeach; ?>

</fieldset>
