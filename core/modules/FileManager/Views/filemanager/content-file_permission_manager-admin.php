
<div class="nav-flex">
    <div class="nav-flex-right">
        <a href="<?php echo $link_add; ?>" class="btn btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a files permission'); ?>
        </a>
    </div>
</div>

<?php echo $form->form_open(); ?>
<?php echo $form->form_input('__method'); ?>
<fieldset class="responsive">
    <table class="table table-hover table-striped table-responsive file_permission_manager-table">
        <thead>
            <tr>
                <th><?php echo t('Directory'); ?></th>
                <th><?php echo t('User Roles'); ?></th>
                <th><?php echo t('Directory permissions'); ?></th>
                <th><?php echo t('Files permissions'); ?></th>
                <th><?php echo t('Weight'); ?></th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody data-draggable="sortable" data-onEnd="sortFilePermission" data-handle=".draggable">
        <?php if ($profils): foreach ($profils as $key => $profil): ?>

            <tr>
                <th class="draggable draggable-verticale" data-title="<?php echo t('Directory'); ?>">
                    <i class="fa fa-arrows-alt-v" aria-hidden="true"></i>
                    <?php echo $profil[ 'folder_show' ]; ?>
                    <?php if ($profil[ 'folder_show_sub' ]): ?>

                    <span  data-tooltip="<?php echo t('Sub directories included'); ?>">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </span>
                    <?php endif; ?>

                </th>
                <td data-title="<?php echo t('User Roles'); ?>">
                <?php foreach ($profil[ 'roles' ] as $role): ?>

                    <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>"
                          class="badge-role"
                          style="background-color: <?php echo htmlspecialchars($role[ 'role_color' ]); ?>">
                        <i class="<?php echo htmlspecialchars($role[ 'role_icon' ]); ?>" aria-hidden="true"></i>
                    </span>
                <?php endforeach; ?>

                </td>
                <td data-title="<?php echo t('Directory permissions'); ?>">
                <?php if ($profil[ 'folder_store' ]): ?>

                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Create'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'folder_update' ]): ?>

                    <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'folder_delete' ]): ?>

                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'folder_download' ]): ?>

                    <i class="fa fa-download" aria-hidden="true"></i> <?php echo t('Downlod'); ?><br>
                <?php endif; ?>

                </td>
                <td data-title="<?php echo t('Files permissions'); ?>">
                <?php if ($profil[ 'file_store' ]): ?>

                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Create'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_update' ]): ?>

                    <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_delete' ]): ?>

                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_download' ]): ?>

                    <i class="fa fa-download" aria-hidden="true"></i> <?php echo t('Download'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_clipboard' ]): ?>

                    <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Copy link'); ?><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_copy' ]): ?>

                    <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Deplace or copy'); ?><br>
                <?php endif; ?>

                </td>
                <td data-title="<?php echo t('Weight'); ?>">
                    <?php echo $form->form_group("profil_{$profil[ 'profil_file_id' ]}-group"); ?>
                </td>
                <td class="cell-actions" data-title="<?php echo t('Actions'); ?>">
                    <div class="btn-group" role="group" aria-label="action">
                        <a class="btn btn-action" href="<?php
                            echo $router->generateUrl('filemanager.permission.edit', [
                                'id' => $profil[ 'profil_file_id' ] ]);
                        ?>">
                            <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>

                        </a>

                        <div class="dropdown">
                            <button class="btn btn-action" data-toogle="dropdown" data-target="#btn-<?php echo $key; ?>" type="button">
                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                            </button>

                            <ul id="btn-<?php echo $key; ?>" class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a class="btn btn-action dropdown-item" href="<?php
                                        echo $router->generateUrl('filemanager.permission.remove', [
                                            'id' => $profil[ 'profil_file_id' ] ]);
                                    ?>">
                                        <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>

                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>

            <tr class="content-nothing">
                <td colspan="6" class="alert alert-info">
                    <i class="fa fa-inbox" aria-hidden="true"></i>
                    <p><?php echo t('Your site does not have a file profile at this time.'); ?></p>
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</fieldset>
<?php echo $form->form_group('submit-group'); ?>
<?php echo $form->form_close(); ?>