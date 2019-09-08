
<a href="<?php echo $linkAdd; ?>" class="btn btn-primary">
    <i class="fa fa-plus" aria-hidden="true"></i> Ajouter du contenu
</a>
<fieldset class="responsive">
    <legend>Mes Contenus</legend>
    <table class="table table-hover table-node">
        <thead>
            <tr class="form-head">
                <th>Nom</th>
                <th>Date de création</th>
                <th class="text-right">Actions</th>
                <th>Publié</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($nodes): ?>
                <?php foreach ($nodes as $node): ?>

                    <tr>
                        <th>
                            <a href="<?php echo $node[ 'link_edit' ]; ?>"><?php echo $node[ 'title' ]; ?></a> <small><?php echo $node[ 'type' ]; ?></small>
                        </th>
                        <td data-title="Date de création"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'created' ]); ?></td>
                        <td data-title="Actions" class="text-right">
                            <div class="btn-group" role="group" aria-label="action">
                                <a href=" <?php echo $node[ 'link_view' ]; ?>" class="btn btn-action" target="_blank">
                                    <span class="fa fa-search"></span> Voir
                                </a>
                                <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                                    <span class="fa fa-edit"></span> Éditer
                                </a>
                                <a href="<?php echo $node[ 'link_delet' ]; ?>" class="btn btn-action" onclick="return confirm('Voulez vous supprimer définitivement le contenu ?')">
                                    <span class="fa fa-times"></span> Supprimer
                                </a>
                            </div>
                        </td>
                        <td data-title="Publié">
                            <?php if ($node[ 'published' ]): ?>

                                <div class="icon-publish" data-tooltip="Publié"></div>
                            <?php else: ?>

                                <div class="icon-notPublish" data-tooltip="Non publié"></div>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>

                <tr>
                    <td colspan="5">
                        Votre site ne possède aucun contenu pour le moment.
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</fieldset>
