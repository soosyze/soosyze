<fieldset class="responsive">
    <?php if ($count >= 1): ?>
        <legend><?php echo t($count > 1 ? ':count users' : ':count user', [':count' => $count]); ?></legend>
    <?php endif; ?>

    <table class="table table-hover table-striped table-responsive table-user_management user_manager-table">
        <thead>
            <tr class="form-head">
                <th>
                <?php if ($order_by === 'username'): ?>
                    <a href="<?php echo $link_username_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('User name'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_username_sort; ?>" class="sort">
                        <?php echo t('User name'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th>
                <?php if ($order_by === 'actived'): ?>
                    <a href="<?php echo $link_actived_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Status'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_actived_sort; ?>" class="sort">
                        <?php echo t('Status'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th>
                <?php if ($order_by === 'time_installed'): ?>
                    <a href="<?php echo $link_time_installed_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Registration date'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_time_installed_sort; ?>" class="sort">
                        <?php echo t('Registration date'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th>
                <?php if (empty($order_by)): ?>
                    <a href="<?php echo $link_time_access_sort; ?>" title="<?php echo t('Descending'); ?>" class="sort">
                        <?php echo t('Date of last access'); ?> <i class="fa fa-sort-amount-down" aria-hidden="true"></i>
                    </a>
                <?php elseif ($order_by === 'time_access'): ?>
                    <a href="<?php echo $link_time_access_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Date of last access'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_time_access_sort; ?>" class="sort">
                        <?php echo t('Date of last access'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>

            <tr>
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
    </table>
</fieldset>

<?php echo $paginate; ?>