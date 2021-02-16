
<div class="block-list">
    <input type="text" id="search"
           class="form-control"
           placeholder="<?php echo t('Search blocks'); ?>"
           aria-label="<?php echo t('Search blocks'); ?>"
           onkeyup="search_blocks();">
</div>
<?php echo $form->form_open(); ?>

<div class="block-list cards-block">
    <?php foreach ($blocks as $key => $block): ?>

    <div class="card__block search_item">
        <h3 class="search_text"><?php echo t($block[ 'title' ]); ?></h3>
        <div class="block-body">
            <?php echo $form->form_html("key_block-$key-content"); ?>

        </div>
        <div class="block-footer">
            <?php echo $form->form_group("key_block-$key-group"); ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="col-md-12">
        <?php echo $form->form_group('submit-group'); ?>

    </div>
</div>
<?php echo $form->form_close(); ?>