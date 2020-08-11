
<tbody>
<?php if ($users): ?>
    <?php foreach ($users as $user): ?>

    <tr>
        <th>#<?php echo $user[ 'user_id' ] ?></th>
        <td data-title="<?php echo t('Username'); ?>">
            <a href="<?php echo $user[ 'link_show' ] ?>"><?php echo $user[ 'username' ] ?></a>
            <?php foreach ($user[ 'roles' ] as $role): ?>

                <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                    <i class="<?php echo $role[ 'role_icon' ]; ?>" aria-hidden="true"></i>
                </span>
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
<?php else: ?>

    <tr>
        <td colspan="6" class="alert alert-info">
            <div class="content-nothing">
                <i class="fa fa-inbox"></i>
                <p><?php echo t('No results were found for your search.'); ?><p>
            </div>
        </td>
    </tr>
<?php endif; ?>
</tbody>
