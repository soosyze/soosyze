
<div class="row">
    <div class="col-md-3 sticky">
        <?php echo $section['submenu']; ?>
    </div>
    <div class="col-md-9">
        <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter un lien
        </a>
        <?php echo $form->form_open(); ?>
        <fieldset class="responsive">
            <legend><?php echo $menuName; ?></legend>
            <?php if ($menu): ?>

                <?php echo $menu; ?>
            <?php else: ?>

            <div class="alert alert-info">Votre menu ne contient aucun lien</div>
            <?php endif; ?>

        </fieldset>
        <?php echo $form->form_token('token_menu'); ?>
        <?php echo $form->form_input('submit'); ?>
        <?php echo $form->form_close(); ?>
    </div>
</div> <!-- /.row -->
