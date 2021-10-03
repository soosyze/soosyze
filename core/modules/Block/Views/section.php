<?php if (!empty($content) || $is_admin): ?>

<div id="section-<?php echo $section_id; ?>" class="section">
    <?php if ($is_admin): ?>
    <div data-id="<?php echo $section_id; ?>"
         data-draggable="sortable"
         data-onEnd="sortSection"
         data-group="section"
         data-handle=".fa-arrows-alt"
         data-ghostClass="placeholder">
    <?php endif; ?>
    <?php foreach ($content as $block): ?>

        <div id="block-<?php echo $block['block_id']; ?>" class="block <?php echo htmlspecialchars($block[ 'class' ]); ?>">
            <?php if ($is_admin) : ?>

            <span class="block-actions">
                <a data-target="#modal_block"
                   data-toogle="modal"
                   class="mod"
                   href="<?php echo $block[ 'link_edit' ]; ?>"
                   title="<?php echo t('Edit'); ?>"><i class="fa fa-edit" aria-hidden="true"></i></a>
                <a data-target="#modal_block"
                   data-toogle="modal"
                   class="mod"
                   href="<?php echo $block[ 'link_remove' ]; ?>"
                   title="<?php echo t('Delete'); ?>"><i class="fa fa-trash-alt" aria-hidden="true"></i></a>
                <i aria-hidden="true"
                   class="fa fa-arrows-alt"
                   data-link_update="<?php echo $block[ 'link_update' ]; ?>"
                   style="cursor: move"
                   title="<?php echo t('Move'); ?>"></i>
            </span>
            <?php endif; ?>
            <?php if (!empty($block[ 'is_title' ])): ?>

            <header class="major">
                <h2><?php echo xss($block[ 'title' ]); ?></h2>
            </header>
            <?php endif; ?>
            <?php if (empty($block['content'])): ?>

            <div class="block-content-disabled">
                <?php echo t('No content available for :title_admin block', [
                    ':title_admin' => $block[ 'title_admin' ] ?? ''
                ]); ?>
            </div>
            <?php else: ?>
                <?php echo $block['hook'] === null ? xss($block[ 'content' ]) : $block['content']; ?>
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
    <?php if ($is_admin): ?>

    </div>
    <?php endif; ?>
    <?php if ($link_create): ?>

    <div class="block-actions">
        <a data-target="#modal_block"
           data-toogle="modal"
           class="btn btn-success mod"
           href="<?php echo $link_create; ?>">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a block'); ?>
        </a>
    </div>
    <?php endif; ?>

</div>
<?php endif; ?>