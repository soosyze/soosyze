<div class="row">
    <div class="col-sm-12">
        <a class="btn btn-primary" href="<?php echo $link_add; ?>">Ajouter un nouveau role</a>
        <fieldset class="responsive">
            <legend>Rôles utilisateurs</legend>
            <table class="table table-hover">
                <thead>
                    <tr class="form-head">
                        <th><?php echo count($roles) ?> Roles</th>
                        <th>Description</th>
                        <th>Poids</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <th>
                                <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span>
                                <?php echo $role[ 'role_label' ] ?>
                            </th>
                            <td data-title="Description"><i><?php echo $role[ 'role_description' ] ?></i></td>
                            <td data-title="Poids"><?php echo $role[ 'role_weight' ]; ?></td>
                            <td data-title="Actions">
                                <a class="btn btn-action" href="<?php echo $role[ 'link_edit' ] ?>">
                                    <i class="fa fa-edit" aria-hidden="true"></i> Éditer
                                </a>
                                <?php if (isset($role[ 'link_remove' ])): ?>
                                    <a class="btn btn-action" href="<?php echo $role[ 'link_remove' ] ?>">
                                        <i class="fa fa-times" aria-hidden="true"></i> Supprimer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </fieldset>
    </div>
</div>