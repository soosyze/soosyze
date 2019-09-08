
<?php echo $form->form_open(); ?>

<fieldset>
    <legend>Profil d'installation</legend>
    <?php $i = 0; ?>
    <?php foreach ($profils as $key => $profil): ?>
        <?php
        echo ($i % 2 === 0)
            ? '<div class="row">'
            : ''
        ?>

        <div class="col-md-6">
            <div class="profil-item">
                <h3><?php echo $profil[ 'title' ]; ?></h3>
                <p><?php echo $profil['description']; ?></p>
                <label class="block-body" for="<?php echo $key; ?>">
                    <?php echo $form->form_group("profil-$key"); ?>
                </label>
            </div>
        </div>
        <?php
        echo ($i % 2 === 1)
            ? '</div>'
            : ''
        ?>
        <?php ++$i; ?>
    <?php endforeach; ?>

</fieldset>
<?php echo $form->form_input('token_install'); ?>
<?php echo $form->form_input('submit'); ?>
<?php echo $form->form_close(); ?>