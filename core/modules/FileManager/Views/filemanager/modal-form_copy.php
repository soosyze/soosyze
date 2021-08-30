
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo htmlspecialchars($title); ?></h2>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?php echo not_empty_or($menu); ?>

            <?php echo $form->form_open(); ?>

            <?php echo $content; ?>

            <?php echo $form->form_input('token_file_copy'); ?>
            <?php echo $form->form_close(); ?>
        </div>
    </div>
</div>