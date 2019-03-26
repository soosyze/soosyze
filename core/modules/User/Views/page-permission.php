<div class="row">
    <div class="col-sm-12">
        <div class="form-group">
            <input type="text" id="search" class="form-control" placeholder="Rechercher permission (exemple: voir, éditer, supprimer...)" onkeyup="searchPermission();">
        </div>
        <form method="post" action="<?php echo $link_update ?>">
            <fieldset class="table-responsive">
                <legend>Permissions utilisateurs</legend>
                <table class="table">
                    <thead class="div-thead">
                        <tr class="form-head">
                            <th>Droit</th>
                            <?php foreach ($roles as $role): ?>

                                <th><?php echo $role[ 'role_label' ]; ?></th>
                            <?php endforeach; ?>

                        </tr>
                    </thead>
                    <tbody id="table-permission">
                        <?php foreach ($modules as $key => $module): ?>

                            <tr><td colspan="<?php echo $colspan; ?>" class="permission-module"><?php echo $key; ?></td></tr>
                            <?php foreach ($module as $key => $permission): ?>

                            <tr id="<?php echo $key ?>">
                                <th><?php echo $permission[ 'action' ] ?></th>
                                <?php foreach ($permission[ 'roles' ] as $role => $checked): ?>
                                    <?php $name = $role . '[' . $key . ']' ?>

                                <td>
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
        var reg = new RegExp(input, 'i');
        Object.keys(modules).forEach(function (module)
        {
            Object.keys(modules[module]).forEach(function (permission) {
                document.getElementById(permission).style.display = !reg.test(modules[module][permission].action)
                        ? "none"
                        : "";
            });
        });
    }
</script>