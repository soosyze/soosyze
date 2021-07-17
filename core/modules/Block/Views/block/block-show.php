
<div id="block-<?php echo $block[ 'block_id' ]; ?>" class="block <?php echo htmlspecialchars($block[ 'class' ]); ?>" data-weight="<?php echo htmlspecialchars($block[ 'weight' ]); ?>">
    <span class="block-actions">
        <a data-target="#modal_block"
           data-toogle="modal"
           class="mod"
           href="<?php echo $block[ 'link_edit' ]; ?>"
           title="<?php echo t('Edit'); ?>"><i class="fa fa-edit" aria-hidden="true"></i></a>
        <a data-target="#modal_block"
           data-toogle="modal"
           class="mod"
           href="<?php echo $block[ 'link_delete' ]; ?>"
           title="<?php echo t('Delete'); ?>"><i class="fa fa-trash-alt" aria-hidden="true"></i></a>
        <i aria-hidden="true"
           class="fa fa-arrows-alt"
           data-link_update="<?php echo $block[ 'link_update' ]; ?>"
           title="<?php echo t('Move'); ?>"></i>
    </span>
    <?php if ($block[ 'title' ]): ?>

    <header class="major">
        <h2><?php echo xss($block[ 'title' ]); ?></h2>
    </header>
    <?php endif; ?>
    <?php if (empty($block['content'])): ?>

    <div class="block-content-disabled">
        <?php echo t('No content available for this block'); ?>
    </div>
    <?php else: ?>
        <?php echo $block['hook'] === null ? xss($block[ 'content' ]) : $block['content']; ?>
    <?php endif; ?>

</div>