
<div class="row">
    <?php if (!empty($form)): ?>
    <div class="col-md-3">
        <?php echo $block[ 'menu_config' ]; ?>
    </div>
    <div class="col-md-9">
        <?php echo $form->renderForm() ?>
    </div>
    <?php endif; ?>
</div>