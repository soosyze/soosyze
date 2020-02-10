<?php if ($edit || !empty($content)): ?>
<div class="section">
    <div class="block-sortable" data-id="<?php echo $section_id; ?>">
    <?php foreach ($content as $block): ?>

        <div class="block <?php echo $block[ 'class' ]; ?>">
            <?php if ($edit) : ?>

            <span class="block-actions">
                <i title="<?php echo t('Edit'); ?>" class="fa fa-edit" data-link_edit="<?php echo $block[ 'link_edit' ]; ?>"></i>
                <i title="<?php echo t('Delete'); ?>" class="fa fa-trash-alt" data-link_delete="<?php echo $block[ 'link_delete' ]; ?>"></i>
                <i title="<?php echo t('Move'); ?>" class="fa fa-arrows-alt" data-link_update="<?php echo $block[ 'link_update' ]; ?>"></i>
            </span>
            <?php endif; ?>
            <?php if ($block[ 'title' ]): ?>

            <header class="major">
                <h2><?php echo $block[ 'title' ]; ?></h2>
            </header>
            <?php endif; ?>
            <?php echo $block[ 'content' ]; ?>

        </div>
    <?php endforeach; ?>

    </div>
    <?php if ($edit) : ?>

    <button class="btn btn-success block-create" 
            data-toogle="modal" 
            data-target="#modal_block" 
            data-link_create="<?php echo $link_create; ?>" 
            data-id="<?php echo $section_id; ?>">
        <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a block'); ?>
    </button>
    <?php endif; ?>

</div>
<?php endif; ?>