<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs">
            <li><a href="<?php echo $link_add ?>">Ajouter un utilisateur</a></li>
            <?php if ($granted_permission): ?>
                <li><a href="<?php echo $link_role ?>">Administrer les rôles</a></li>
                <li><a href="<?php echo $link_permission ?>">Administrer les permissions</a></li>
            <?php endif; ?>
        </ul>
        <fieldset class="table-responsive">
            <legend>Gestion des utilisateurs</legend>
            <table class="table">
                <thead class="div-thead">
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
                            <td>
                                <a href="<?php echo $user[ 'link_show' ] ?>"><?php echo $user[ 'username' ] ?></a>
                                <?php foreach ($user[ 'roles' ] as $role): ?>

                                    <span title="<?php echo $role[ 'role_label' ]; ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span>
                                <?php endforeach; ?>

                            </td>
                            <td>
                                <?php if ($user[ 'actived' ] == 1): ?>
                                    actif
                                <?php else: ?>
                                    inactif
                                <?php endif; ?>

                            </td>
                            <td><?php echo date('d/m/Y', $user[ 'time_installed' ]) ?></td>
                            <td>
                                <?php if ($user[ 'time_access' ]): ?>
                                    <?php echo date('d/m/Y', $user[ 'time_access' ]) ?>
                                <?php else: ?>
                                    Jamais
                                <?php endif; ?>

                            </td>
                            <td>
                                <a class="btn btn-action" href="<?php echo $user[ 'link_edit' ] ?>">Éditer</a>
                                <a class="btn btn-action" href="<?php echo $user[ 'link_remove' ] ?>">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </fieldset>
    </div>
</div>