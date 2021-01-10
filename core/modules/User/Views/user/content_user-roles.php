
<fieldset>
    <legend><?php echo t('Roles'); ?></legend>

    <?php foreach ($roles as $role): ?>

        <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
            <i class="<?php echo $role[ 'role_icon' ]; ?>" aria-hidden="true"></i>
        </span>
    <?php endforeach; ?>

</fieldset>