
<div class="block-list">
    <input type="text" id="search" class="form-control" placeholder="<?php echo t('Search blocks'); ?>" aria-label="<?php echo t('Search blocks'); ?>" onkeyup="search_blocks();">
</div>
<?php echo $form->form_open(); ?>

<div class="row block-list">
    <?php foreach ($blocks as $key => $block): ?>

    <div class="col-md-4 block-item search_item">
        <h3 class="search_text"><?php echo t($block[ 'title' ]); ?></h3>
        <label class="block-body" for="key_block-<?php echo $key; ?>">
            <?php echo $form->form_group("key_block-$key-group"); ?>

        </label>
    </div>
    <?php endforeach; ?>

    <div class="col-md-12">
        <?php echo $form->form_group('submit-group'); ?>

    </div>
</div>
<?php echo $form->form_close(); ?>