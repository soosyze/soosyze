
<a class="btn btn-primary" href="<?php echo $link_add; ?>">
    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a new role'); ?>
</a>
<fieldset class="responsive">
    <legend><?php echo t('User Roles'); ?></legend>
    <table class="table table-hover">
        <thead>
            <tr class="form-head">
                <th><?php echo count($roles) ?> <?php echo t('Role(s)'); ?></th>
                <th><?php echo t('Description'); ?></th>
                <th><?php echo t('Weight'); ?></th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>

            <tr>
                <th>
                    <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                        <i class="<?php echo $role['role_icon']; ?>" aria-hidden="true"></i>
                    </span>
                    <?php echo t($role[ 'role_label' ]); ?>

                </th>
                <td data-title="<?php echo t('Description'); ?>"><em><?php echo t($role[ 'role_description' ]); ?></em></td>
                <td data-title="<?php echo t('Weight'); ?>"><?php echo $role[ 'role_weight' ]; ?></td>
                <td data-title="<?php echo t('Actions'); ?>">
                    <a class="btn btn-action" href="<?php echo $role[ 'link_edit' ]; ?>">
                        <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
                    </a>
                    <?php if (isset($role[ 'link_remove' ])): ?>
                    <a class="btn btn-action" href="<?php echo $role[ 'link_remove' ]; ?>">
                        <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
                    </a>
                    <?php endif; ?>

                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</fieldset>