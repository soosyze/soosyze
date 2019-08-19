
<div class="block" data-weight="<?php echo $block[ 'weight' ]; ?>">
    <span class="block-actions">
        <i aria-hidden="true" class="fa fa-edit" data-link_edit="<?php echo $block[ 'link_edit' ]; ?>"></i>
        <i aria-hidden="true" class="fa fa-trash-alt" data-link_delete="<?php echo $block[ 'link_delete' ]; ?>"></i>
        <i aria-hidden="true" class="fa fa-arrows-alt" data-link_update="<?php echo $block[ 'link_update' ]; ?>"></i>
    </span>
    <?php if ($block[ 'title' ]): ?>

        <header class="major">
            <h2><?php echo $block[ 'title' ]; ?></h2>
        </header>
    <?php endif; ?>
    <?php echo $block[ 'content' ]; ?>

</div>