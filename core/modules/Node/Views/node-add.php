
<fieldset>
    <legend>Type de contenu</legend>
    <?php foreach ($node_type as $node): ?>

    <h3><a href="<?php echo $node[ 'link' ]; ?>"><?php echo $node[ 'node_type_name' ]; ?></a></h3>
    <p><?php echo $node[ 'node_type_description' ]; ?></p>
    <?php endforeach; ?>

</fieldset>
