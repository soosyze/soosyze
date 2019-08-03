
<?php echo $form->renderForm() ?>
<?php if ($granted_register): ?>
    <p><a href="<?php echo $url_register ?>">Inscription utilisateur</a></p>
<?php endif; ?>
<?php if ($granted_relogin): ?>
    <p><a href="<?php echo $url_relogin ?>">Mot de passe perdu ?</a></p>
<?php endif; ?>