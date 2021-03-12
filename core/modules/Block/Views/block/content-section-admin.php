
<div class="block-content-disabled">
    <a href="<?php echo $link_section; ?>" class="btn btn-primary">
        <?php echo t($content); ?>

    </a>
    <a href="<?php echo $link_theme_index; ?>" class="btn btn-danger">
        <?php echo t('Back to administration'); ?>

    </a>
</div>

<div id="modal_block" class="modal" role="dialog" aria-label="<?php echo t('Block creation window.'); ?>">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
                <h2><?php echo t('Add a block'); ?></h2>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>