
<div class="row">
    <div class="col-md-12">
        <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">Ajouter du contenu</a>
    </div>
    <div class="col-sm-12">
        <fieldset>
            <legend>Mes Contenus</legend>
            <div class="div-thead row">
                <div class="col-md-4">Nom</div>
                <div class="col-md-2">Date de création</div>
                <div class="col-md-2">Date de modification</div>
                <div class="col-md-3">Actions</div>
                <div class="col-md-1">Publié</div>
            </div>
        <?php if ($nodes): ?>
            <?php foreach ($nodes as $node): ?>

            <div class="div-tbody row">
                <div class="col-md-4">
                    <h3><a href="<?php echo $node[ 'link_view' ]; ?>"><?php echo $node[ 'title' ]; ?></a> <small><?php echo $node[ 'type' ]; ?></small></h3>
                </div>
                <div class="col-md-2"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'created' ]); ?></div>
                <div class="col-md-2"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'changed' ]); ?></div>
                <div class="col-md-3">
                </div>
                <div class="col-md-1">
                <?php if ($node[ 'published' ] == 'on'): ?>
                            <div class="btn-group" role="group" aria-label="action">
                                <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                                    <span class="fa fa-edit"></span> Éditer
                                </a>
                                <a href="<?php echo $node[ 'link_delet' ]; ?>" class="btn btn-action" onclick="return confirm('Voulez vous supprimer définitivement le contenu ?')">
                                    <span class="fa fa-times"></span> Supprimer
                                </a>
                            </div>

                            <div class="icon-publish">
                                <span class="fa fa-ok" aria-hidden="Publish"></span>
                            </div>
                        <?php else: ?>

                            <div class="icon-notPublish">
                                <span class="fa fa-times" aria-hidden="Not publish"></span>
                            </div>
                        <?php endif; ?>

                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>

            <div class="div-tbody row">
                <div class="col-md-12">
                    Votre site ne possède aucun contenu pour le moment.
                </div>
            </div>
        <?php endif; ?>

        </fieldset>
    </div>
</div> <!-- /.row -->
