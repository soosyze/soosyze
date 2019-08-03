
<ul class="nav nav-tabs">
    <li><a href="<?php echo $link_add ?>">Ajouter un utilisateur</a></li>

    <?php if ($granted_permission): ?>
    <li><a href="<?php echo $link_role ?>">Administrer les rôles</a></li>
    <li><a href="<?php echo $link_permission ?>">Administrer les permissions</a></li>
    <?php endif; ?>
</ul>
<fieldset class="responsive">
    <legend>Gestion des utilisateurs</legend>
    <table class="table table-hover table-user_management">
        <thead>
            <tr class="form-head">
                <th>Id</th>
                <th>Nom utilisateur</th>
                <th>Statut</th>
                <th>Date d'inscription</th>
                <th>Date du dernier accès</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>

            <tr>
                <th>#<?php echo $user[ 'user_id' ] ?></th>
                <td data-title="Nom utilisateur">
                    <a href="<?php echo $user[ 'link_show' ] ?>"><?php echo $user[ 'username' ] ?></a>
                    <?php foreach ($user[ 'roles' ] as $role): ?>

                    <span data-tooltip="<?php echo $role[ 'role_label' ]; ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span>
                    <?php endforeach; ?>

                </td>
                <td data-title="Statut">
                    <?php echo $user[ 'actived' ] == 1 ? 'actif' : 'inactif'; ?>

                </td>
                <td data-title="Date d'inscription"><?php echo date('d/m/Y', $user[ 'time_installed' ]) ?></td>
                <td data-title="Date du dernier accès">
                    <?php echo $user[ 'time_access' ] ? date('d/m/Y', $user[ 'time_access' ]) : 'Jamais'; ?>

                </td>
                <td data-title="Actions">
                    <a class="btn btn-action" href="<?php echo $user[ 'link_edit' ] ?>">
                        <i class="fa fa-edit"></i> Éditer
                    </a>
                    <a class="btn btn-action" href="<?php echo $user[ 'link_remove' ] ?>">
                        <i class="fa fa-times"></i> Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>
</fieldset>