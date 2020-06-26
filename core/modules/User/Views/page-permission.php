
<?php echo $user_manager_submenu; ?>

<div class="form-group">
    <input type="text"
           id="search"
           class="form-control"
           placeholder="<?php echo t('Search permissions'); ?>"
           aria-label="<?php echo t('Search permissions'); ?>"
           onkeyup="searchPermission();"
           autofocus>
</div>
<form method="post" action="<?php echo $link_update ?>">
    <fieldset class="responsive">
        <legend><?php echo t('User permissions'); ?></legend>
        <table class="table table-hover">
            <thead>
                <tr class="form-head">
                    <th><?php echo t('Name'); ?></th>
                    <?php foreach ($roles as $key => $role): ?>

                    <th id="role-<?php echo $role['role_id']; ?>">
                        <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                            <i class="<?php echo $role['role_icon']; ?>" aria-hidden="true"></i></span>
                            <?php echo t($role[ 'role_label' ]); ?></th>
                    <?php endforeach; ?>

                </tr>
            </thead>
            <tbody id="table-permission">
                <?php foreach ($modules as $key => $module): ?>

                <tr><td id="<?php echo $key; ?>" colspan="<?php echo $colspan; ?>" class="permission-module"><?php echo t($key); ?></td></tr>
                <?php foreach ($module as $key => $permission): ?>

                <tr id="<?php echo $key ?>">
                    <th><?php echo $permission[ 'action' ] ?></th>
                    <?php foreach ($permission[ 'roles' ] as $role => $checked): ?>
                    <?php $name = $role . '[' . $key . ']' ?>

                    <td data-title="<?php echo t($roles[ $role - 1 ][ 'role_label' ]); ?>">
                        <input type="checkbox" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $key ?>" <?php echo $checked ?> aria-labelledby="role-<?php echo $role; ?>">
                        <label for="<?php echo $name ?>"><i class="ui" aria-hidden="true"></i></label>
                    </td>
                    <?php endforeach; ?>

                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>

            </tbody>
        </table>
    </fieldset>
    <input type="submit" name="submit" class="btn btn-success" value="<?php echo t('Save'); ?>">
</form>
<script>
    var modules = <?php echo json_encode($modules) ?>;
    function searchPermission()
    {
        var input = document.getElementById('search').value;
        var reg = new RegExp(input, 'i');
        Object.keys(modules).forEach(function (module)
        {
            var module_hide = true;
            Object.keys(modules[module]).forEach(function (permission) {
                if (!reg.test(modules[module][permission].action)) {
                    document.getElementById(permission).style.display = "none";
                } else {
                    document.getElementById(permission).style.display = "";
                    module_hide = false;
                }
            });

            document.getElementById(module).style.display = module_hide
                    ? "none"
                    : "";
        });
    }
</script>