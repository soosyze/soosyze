
<?php echo $user_manager_submenu; ?>

<div class="nav-flex">
    <div class="nav-flex-right">
        <a href="<?php echo $link_add; ?>" class="btn btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a files permission'); ?>
        </a>
    </div>
</div>

<?php echo $form->form_open(); ?>
<fieldset class="responsive">
    <legend><?php echo t('User file permissions'); ?></legend>
    <table class="table table-hover">
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
        <tbody class="nested-sortable-file_permission" id="main_sortable">
        <?php if ($profils): ?>
            <?php foreach ($profils as $profil): ?>

            <tr>
                <th class="draggable draggable-verticale" data-title="<?php echo t('Directory'); ?>">
                    <i class="fa fa-arrows-alt-v"></i>
                    <?php echo $profil[ 'folder_show' ]; ?>
                    <?php if ($profil[ 'folder_show_sub' ]): ?>

                    <span  data-tooltip="<?php echo t('Sub directories included'); ?>">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </span>
                    <?php endif; ?>

                </th>
                <td data-title="<?php echo t('User Roles'); ?>">
                <?php foreach ($profil[ 'roles' ] as $role): ?>

                    <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                        <i class="<?php echo $role[ 'role_icon' ]; ?>" aria-hidden="true"></i>
                    </span>
                <?php endforeach; ?>

                </td>
                <td data-title="<?php echo t('Directory permissions'); ?>">
                <?php if ($profil[ 'folder_store' ]): ?>

                    <i class="fa fa-plus" aria-hidden="true"> <?php echo t('Add'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'folder_update' ]): ?>

                    <i class="fa fa-edit" aria-hidden="true"> <?php echo t('Edit'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'folder_delete' ]): ?>

                    <i class="fa fa-times" aria-hidden="true"> <?php echo t('Delete'); ?></i><br>
                <?php endif; ?>

                </td>
                <td data-title="<?php echo t('Files permissions'); ?>">
                <?php if ($profil[ 'file_store' ]): ?>

                    <i class="fa fa-plus" aria-hidden="true"> <?php echo t('Add'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_update' ]): ?>

                    <i class="fa fa-edit" aria-hidden="true"> <?php echo t('Edit'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_delete' ]): ?>

                    <i class="fa fa-times" aria-hidden="true"> <?php echo t('Delete'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_download' ]): ?>

                    <i class="fa fa-download" aria-hidden="true"> <?php echo t('Download'); ?></i><br>
                <?php endif; ?>
                <?php if ($profil[ 'file_clipboard' ]): ?>

                    <i class="fa fa-copy" aria-hidden="true"> <?php echo t('Copy link'); ?></i><br>
                <?php endif; ?> 
                </td>
                <td data-title="<?php echo t('Weight'); ?>">
                    <?php echo $form->form_group("profil_{$profil[ 'profil_file_id' ]}-group"); ?>
                </td>
                <td data-title="<?php echo t('Actions'); ?>">
                    <a class="btn btn-action" href="<?php
                        echo $router->getRoute('filemanager.profil.edit', [
                            ':id' => $profil[ 'profil_file_id' ] ]);
                    ?>">
                        <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>

                    </a>
                    <a class="btn btn-action" href="<?php
                        echo $router->getRoute('filemanager.profil.remove', [
                            ':id' => $profil[ 'profil_file_id' ] ]);
                    ?>">
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
                        <p><?php echo t('Your site does not have a file profile at this time.'); ?></p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</fieldset>
<?php echo $form->form_input('token_profil_form'); ?>
<?php echo $form->form_input('submit'); ?>
<?php echo $form->form_close(); ?>