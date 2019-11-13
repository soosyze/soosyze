
<div class="row">
    <div class="col-md-3 sticky">
        <?php echo $section['submenu']; ?>
    </div>
    <div class="col-md-9">
        <div class="action_bar">
            <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">
                <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a link'); ?>
            </a>
        </div>
        <?php echo $form->form_open(); ?>

        <fieldset class="responsive">
            <legend><?php echo t($menuName); ?></legend>
            <?php if ($menu): ?>

                <?php echo $menu; ?>
            <?php else: ?>

            <div class="alert alert-info"><?php echo t('The menu contains no links'); ?></div>
            <?php endif; ?>

        </fieldset>
        <?php echo $form->form_token('token_menu'); ?>
        <?php echo $form->form_input('submit'); ?>
        <?php echo $form->form_close(); ?>

    </div>
</div> <!-- /.row -->
