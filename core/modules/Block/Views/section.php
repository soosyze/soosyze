<?php if (!empty($content) || $is_admin): ?>

<div class="section">
    <?php if ($is_admin): ?>

    <div class="block-sortable" data-id="<?php echo $section_id; ?>">
    <?php endif; ?>
    <?php foreach ($content as $block): ?>

        <div class="block <?php echo $block[ 'class' ]; ?>">
            <?php if ($is_admin) : ?>

            <span class="block-actions">
                <i title="<?php echo t('Edit'); ?>"
                   class="fa fa-edit"
                   data-link_edit="<?php echo $block[ 'link_edit' ]; ?>"
                   aria-hidden="true"></i>
                <i title="<?php echo t('Delete'); ?>"
                   class="fa fa-trash-alt"
                   data-link_delete="<?php echo $block[ 'link_delete' ]; ?>"
                   aria-hidden="true"></i>
                <i title="<?php echo t('Move'); ?>"
                   class="fa fa-arrows-alt"
                   data-link_update="<?php echo $block[ 'link_update' ]; ?>"
                   aria-hidden="true"></i>
            </span>
            <?php endif; ?>
            <?php if ($block[ 'title' ]): ?>

            <header class="major">
                <h2><?php echo $block[ 'title' ]; ?></h2>
            </header>
            <?php endif; ?>

            <?php if (empty($block['content'])): ?>
                <div class="block-content-disabled">
                    <?php echo t('No content available for this block'); ?>
                </div>
            <?php else: ?>
                <?php echo $block[ 'content' ]; ?>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>

    <?php if ($is_admin) : ?>

    </div>
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