
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo $title; ?></h2>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="modal-messages"></div>
            <?php if (!empty($menu)): echo $menu; endif; ?>

            <?php echo $form; ?>
        </div>
    </div>
</div>