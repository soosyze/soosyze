
<div class="block-content-disabled">
    <p><?php echo t($content); ?></p>
    <a href="<?php echo $link_theme; ?>" class="btn btn-primary"><?php echo t('Website theme'); ?></a>
    <a href="<?php echo $link_theme_admin; ?>" class="btn btn-primary"><?php echo t('Website administration theme'); ?></a>
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