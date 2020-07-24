
<?php echo $user_manager_submenu; ?>

<div class="nav-flex">   
    <div class="nav-flex-left">
        <button class="btn" onclick="document.getElementById('filter_user').classList.toggle('hidden')">
            <i class="fa fa-filter"></i> <?php echo t('Filter'); ?>
        </button>
    </div>
    <div class="nav-flex-right">
        <a href="<?php echo $link_create_user; ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> <?php echo t('Add a user'); ?>
        </a>
    </div>
</div>

<div class="hidden" id="filter_user">
    <div class="row">
        <form action="<?php echo $link_filter_user; ?>" method="get" id="form_filter_user">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="username"><?php echo t('User name'); ?></label>
                    <input type="text" name="username" id="username" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="firstname"><?php echo t('First name'); ?></label>
                    <input type="text" name="firstname" id="firstname" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="name"><?php echo t('Name'); ?></label>
                    <input type="text" name="name" id="name" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="actived"><?php echo t('Status'); ?></label>
                    <select name="actived" class="form-control" id="actived">
                        <option value=""><?php echo t('All'); ?></option>
                        <option value="1"><?php echo t('Active'); ?></option>
                        <option value="0"><?php echo t('Inactive'); ?></option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <a href="<?php echo $link_user_admin; ?>" class="btn danger"><?php echo t('Reset'); ?></a>
            </div>
        </div>
    </div>
</div>

<fieldset class="responsive">
    <legend><?php echo t('User Management'); ?></legend>
    <table class="table table-hover table-user_management">
        <thead>
            <tr class="form-head">
                <th>Id</th>
                <th><?php echo t('Username'); ?></th>
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
                <td data-title="<?php echo t('Username'); ?>">
                    <a href="<?php echo $user[ 'link_show' ] ?>"><?php echo $user[ 'username' ] ?></a>
                    <?php foreach ($user[ 'roles' ] as $role): ?>

                    <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                        <i class="<?php echo $role['role_icon']; ?>" aria-hidden="true"></i>
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

        </tbody>
    </table>
</fieldset>