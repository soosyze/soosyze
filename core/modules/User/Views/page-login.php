
<?php echo $form->renderForm() ?>
<?php if ($granted_register): ?>
    <p><a href="<?php echo $url_register ?>"><?php echo t('User registration'); ?></a></p>
<?php endif; ?>
<?php if ($granted_relogin): ?>
    <p><a href="<?php echo $url_relogin ?>"><?php echo t('Forgot your password ?'); ?></a></p>
<?php endif; ?>