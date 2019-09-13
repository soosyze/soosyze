
<ul class="nav nav-tabs">
    <li><a href="<?php echo $link_add ?>"><?php echo t('Add a user'); ?></a></li>

    <?php if ($granted_permission): ?>
    <li><a href="<?php echo $link_role ?>"><?php echo t('Administer roles'); ?></a></li>
    <li><a href="<?php echo $link_permission ?>"><?php echo t('Administer permissions'); ?></a></li>
    <?php endif; ?>
</ul>
<fieldset class="responsive">
    <legend><?php echo t('User Management'); ?></legend>
    <table class="table table-hover table-user_management">
        <thead>
            <tr class="form-head">
                <th>Id</th>
                <th><?php echo t('Name'); ?></th>
                <th><?php echo t('Status'); ?></th>
                <th><?php echo t('Registration date'); ?></th>
                <th><?php echo t('Date of last access'); ?></th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>

            <tr>
                <th>#<?php echo $user[ 'user_id' ] ?></th>
                <td data-title="<?php echo t('Name'); ?>">
                    <a href="<?php echo $user[ 'link_show' ] ?>"><?php echo $user[ 'username' ] ?></a>
                    <?php foreach ($user[ 'roles' ] as $role): ?>

                    <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span>
                    <?php endforeach; ?>

                </td>
                <td data-title="<?php echo t('Status'); ?>">
                    <?php echo $user[ 'actived' ] == 1 ? t('Active') : t('Inactive'); ?>

                </td>
                <td data-title="<?php echo t('Registration date'); ?>"><?php echo date('d/m/Y', $user[ 'time_installed' ]) ?></td>
                <td data-title="<?php echo t('Date of last access'); ?>">
                    <?php echo $user[ 'time_access' ] ? date('d/m/Y', $user[ 'time_access' ]) : t('Never'); ?>

                </td>
                <td data-title="<?php echo t('Actions'); ?>">
                    <a class="btn btn-action" href="<?php echo $user[ 'link_edit' ] ?>">
                        <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
                    </a>
                    <a class="btn btn-action" href="<?php echo $user[ 'link_remove' ] ?>">
                        <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</fieldset>