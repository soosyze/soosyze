
<?php echo $form->form_open(); ?>

<fieldset>
    <legend><?php echo t('Installation profile'); ?></legend>
    <?php $i = 0; ?>
    <?php foreach ($profils as $key => $profil): ?>
        <?php if ($i % 2 === 0): echo '<div class="row">'; endif; ?>

        <div class="col-md-6">
            <div class="profil-item">
                <h3><?php echo $profil[ 'title' ]; ?></h3>
                <p><?php echo $profil['description']; ?></p>
                <img src="<?php echo $profil[ 'img' ]; ?>" src="<?php echo $profil[ 'img' ]; ?>">
                <?php echo $form->form_group("profil_$key-group"); ?>

            </div>
        </div>
    
        <?php if ($i % 2 === 1): echo '</div>'; endif; ?>
        <?php ++$i; ?>
    <?php endforeach; ?>

</fieldset>
<?php echo $form->form_input('token_step_install'); ?>
<?php echo $form->form_input('submit'); ?>
<?php echo $form->form_close(); ?>