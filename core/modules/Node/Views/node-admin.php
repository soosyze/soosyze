
<div class="row">
    <div class="col-md-12">
        <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Ajouter du contenu
        </a>
    </div>
    <div class="col-sm-12">
        <fieldset class="responsive">
            <legend>Mes Contenus</legend>
            <table class="table table-hover table-node">
                <thead>
                    <tr class="form-head">
                        <th>Nom</th>
                        <th>Date de création</th>
                        <th>Date de modification</th>
                        <th>Actions</th>
                        <th>Publié</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($nodes): ?>
                    <?php foreach ($nodes as $node): ?>

                    <tr>
                        <th>
                            <a href="<?php echo $node[ 'link_view' ]; ?>"><?php echo $node[ 'title' ]; ?></a> <small><?php echo $node[ 'type' ]; ?></small>
                        </th>
                        <td data-title="Date de création"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'created' ]); ?></td>
                        <td data-title="Date de modification"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'changed' ]); ?></td>
                        <td data-title="Actions">
                            <div class="btn-group" role="group" aria-label="action">
                                <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                                    <span class="fa fa-edit"></span> Éditer
                                </a>
                                <a href="<?php echo $node[ 'link_delet' ]; ?>" class="btn btn-action" onclick="return confirm('Voulez vous supprimer définitivement le contenu ?')">
                                    <span class="fa fa-times"></span> Supprimer
                                </a>
                            </div>
                        </td>
                        <td data-title="Publié">
                        <?php if ($node[ 'published' ] == 'on'): ?>

                            <div class="icon-publish">
                                <span class="fa fa-ok" aria-hidden="Publish"></span>
                            </div>
                        <?php else: ?>

                            <div class="icon-notPublish">
                                <span class="fa fa-times" aria-hidden="Not publish"></span>
                            </div>
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
    </div>
</div> <!-- /.row -->
