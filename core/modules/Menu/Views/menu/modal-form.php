
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo $title; ?></h2>
</div>

<div class="modal-body <?php echo not_empty_or($class); ?>">
    <?php echo not_empty_or($menu); ?>

    <div class="block-edit">
        <div class="row">
            <?php if (!empty($fieldset_submenu)): ?>
            <div class="col-md-3">
                <?php echo $fieldset_submenu; ?>
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
        </div>
    </div>
</div>
