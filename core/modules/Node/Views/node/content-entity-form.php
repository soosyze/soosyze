
<?php if (!empty($node_submenu)): ?>
    <?php echo $node_submenu; ?>
<?php endif; ?>

<div class="row">
    <?php if (!empty($node_fieldset_submenu)): ?>
    <div class="col-md-3 sticky">
        <?php echo $node_fieldset_submenu; ?>
    </div>
    <div class="col-md-9">
        <div class="tab-content">
            <?php echo $form; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="col-md-12">
        <div class="tab-content">
            <?php echo $form; ?>
        </div>
    </div>
    <?php endif; ?>

    <div id="modal_filemanager" class="modal" role="dialog" aria-label="<?php echo t('File actions window'); ?>">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
</div>