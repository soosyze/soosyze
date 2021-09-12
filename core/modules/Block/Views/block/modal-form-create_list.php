
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo $title; ?></h2>
</div>

<div class="modal-body form-create_list">
    <div class="row">
        <div class="col-md-4">
            <input aria-label="<?php echo t('Search blocks'); ?>"
                   class="form-control block-search"
                   id="search"
                   onkeyup="searchBlocks();"
                   placeholder="<?php echo t('Search blocks'); ?>"
                   type="text">

            <h3><?php echo t('List of blocks'); ?></h3>
            <?php echo $form; ?>

        </div>
        <div class="col-md-8">
            <div class="block-preview">
                <div class="block-preview-no_select"><?php echo t('Select a block from the list to preview it before adding it'); ?></div>
            </div>
        </div>
    </div>
</div>