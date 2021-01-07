
<div class="row">
    <?php if ($filemanager): ?>
    <div class="col-md-12">

        <?php echo $form; ?>
    </div>

    <div class="filemanager">
        <?php echo $filemanager; ?>

    </div>

    <div id="modal_filemanager" class="modal" aria-label="<?php echo t('File actions window'); ?>">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <p><?php echo t('No preview available'); ?></p>
    </div>
    <?php endif; ?>
</div>