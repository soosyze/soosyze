
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo $title; ?></h2>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="modal-messages"></div>
            <?php echo not_empty_or($menu); ?>

            <?php echo $form; ?>

            <?php echo if_or(!empty($is_progress), '<div id="filemanager-dropfile__progress_cards"></div>'); ?>

        </div>
    </div>
</div>