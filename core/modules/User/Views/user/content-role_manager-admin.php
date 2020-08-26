
<?php echo $user_manager_submenu; ?>

<div class="nav-flex">
    <div class="nav-flex-right">
        <a class="btn btn-primary" href="<?php echo $link_add; ?>">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a new role'); ?>
        </a>
    </div>
</div>

<?php echo $form->form_open(); ?>
<fieldset class="responsive">
    <table class="table table-hover table-striped table-responsive role_manager-table">
        <thead>
            <tr class="form-head">
                <th><?php echo count($roles) ?> <?php echo t('Role(s)'); ?></th>
                <th><?php echo t('Description'); ?></th>
                <th><?php echo t('Weight'); ?></th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody id="main_sortable" class="nested-sortable-role">
            <?php foreach ($roles as $role): ?>

            <tr>
                <th class="draggable draggable-verticale">
                    <i class="fa fa-arrows-alt-v" aria-hidden="true"></i>
                    <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                        <i class="<?php echo $role[ 'role_icon' ]; ?>" aria-hidden="true"></i>
                    </span>
                    <?php echo t($role[ 'role_label' ]); ?>

                </th>
                <td data-title="<?php echo t('Description'); ?>"><em><?php echo t($role[ 'role_description' ]); ?></em></td>
                <td data-title="<?php echo t('Weight'); ?>">
                    <?php echo $form->form_group("role_{$role[ 'role_id' ]}-group"); ?>

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
<?php echo $form->form_input('token_role_form'); ?>
<?php echo $form->form_input('submit'); ?>
<?php echo $form->form_close(); ?>