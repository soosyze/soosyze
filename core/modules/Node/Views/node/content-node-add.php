
<fieldset>
    <legend><?php echo t('Type of content'); ?></legend>

    <div class="row">
        <?php foreach ($node_type as $node): ?>
        
        <div class="col-md-3">
            <h3 class="node_type-badge node_type-badge__<?php echo $node[ 'node_type' ]; ?>">
                <i class="<?php echo $node[ 'node_type_icon' ]; ?>" aria-hidden="true"></i> <?php echo t($node[ 'node_type_name' ]); ?>

            </h3>
            <p><?php echo t($node[ 'node_type_description' ]); ?></p>
            <a class="btn btn-primary" href="<?php echo $node[ 'link' ]; ?>">
                <i class="fa fa-plus" aria-hidden="true"></i>
                <?php echo t('Add :name', [ ':name' => t($node[ 'node_type_name' ]) ]); ?>
            
            </a>
            </div>
        <?php endforeach; ?>

    </div>
</fieldset>
