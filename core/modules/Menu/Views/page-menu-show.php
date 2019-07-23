<div class="row">
    <div class="col-md-12">
        <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">Ajouter un lien</a>
        <?php echo $form->form_open(); ?>
        <fieldset class="responsive">
            <legend><?php echo $menuName; ?></legend>

        <?php if ($menu): ?>

            <p>Vous pouvez déplacer vos liens du menu à l'aide de votre souris.</p>
        <?php endif; ?>
        <?php if ($menu): ?>
            <?php echo $menu; ?>
        <?php endif; ?>
                
        </fieldset>
        <?php echo $form->form_token('token_menu'); ?>
        <?php echo $form->form_input('submit'); ?>
        <?php echo $form->form_close(); ?>
    </div>
</div> <!-- /.row -->
