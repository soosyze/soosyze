
<div class="block-edit" data-link_show="<?php echo $link_show; ?>">
    <?php if ($form->form_errors()): ?>
        <?php foreach ($form->form_errors() as $error): ?>

            <div class="alert alert-danger">
                <p><?php echo $error ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if ($form->form_success()): ?>
        <?php foreach ($form->form_success() as $success): ?>

            <div class="alert alert-success">
                <p><?php echo $success ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php echo $form->form_input('submit_cancel'); ?>
    <?php echo $form->renderForm(); ?>

</div>