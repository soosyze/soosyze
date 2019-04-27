<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <input type="text" id="search" class="form-control" placeholder="Rechercher des permissions (exemple: voir, éditer, supprimer...)" onkeyup="searchPermission();" autofocus>
        </div>
        <form method="post" action="<?php echo $link_update ?>">
            <fieldset class="responsive">
                <legend>Permissions utilisateurs</legend>
                <table class="table table-hover">
                    <thead>
                        <tr class="form-head">
                            <th>Droit</th>
                            <?php foreach ($roles as $role): ?>

                            <th><?php echo $role[ 'role_label' ]; ?></th>
                            <?php endforeach; ?>

                        </tr>
                    </thead>
                    <tbody id="table-permission">
                    <?php foreach ($modules as $key => $module): ?>

                        <tr><td id="<?php echo $key; ?>" colspan="<?php echo $colspan; ?>" class="permission-module"><?php echo $key; ?></td></tr>
                        <?php foreach ($module as $key => $permission): ?>

                        <tr id="<?php echo $key ?>">
                            <th><?php echo $permission[ 'action' ] ?></th>
                            <?php foreach ($permission[ 'roles' ] as $role => $checked): ?>
                            <?php $name = $role . '[' . $key . ']' ?>

                            <td data-title="<?php echo $roles[ $role - 1 ][ 'role_label' ]; ?>">
                                <input type="checkbox" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $key ?>" <?php echo $checked ?>>
                                <label for="<?php echo $name ?>"><i class="ui"></i></label>
                            </td>
                            <?php endforeach; ?>

                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </fieldset>
            <input type="submit" name="submit" class="btn btn-success" value="Enregistrer">
        </form>
    </div>
</div>
<script>
    var modules = <?php echo json_encode($modules) ?>;
    function searchPermission()
    {
        var input = document.getElementById('search').value;
        var reg   = new RegExp(input, 'i');
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