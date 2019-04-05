
<div class="row">
    <div class="col-md-12">
        <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">Ajouter un lien</a>
        <?php echo $form->form_open(); ?>
        <fieldset class="table-responsive">
            <legend><?php echo $menuName; ?></legend>
            <table class="table">
                <thead class="div-thead">
                    <tr class="form-head">
                        <th>Lien</th>
                        <th>Activé</th>
                        <th>Poids</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="sortable">
                <?php if ($menu): ?>
                    <?php foreach ($menu as $link): ?>

                    <tr class="draggable">
                        <td>
                            <a href="<?php echo $link[ 'link' ]; ?>" target="<?php echo $link[ 'target_link' ]; ?>" <?php if ($link[ 'target_link' ] === '_blank'): ?> rel="noopener noreferrer" <?php endif; ?>>
                                <?php echo $link[ 'title_link' ]; ?></a>
                        </td>
                        <td>
                            <div>
                                <?php echo $form->form_input('active-' . $link[ 'id' ]); ?>
                                <label for="active-<?php echo $link[ 'id' ]; ?>"><span class="ui"></span>&nbsp;</label>
                            </div>
                        </td>
                        <td>
                            <?php echo $form->form_input('weight-' . $link[ 'id' ]); ?>
                        </td>	
                        <td>
                            <a class="btn btn-action" href="<?php echo $link[ 'link_edit' ]; ?>">
                                <i class="fa fa-edit"></i> Éditer
                            </a>
                            <a class="btn btn-action" href="<?php echo $link[ 'link_delete' ]; ?>" onclick="return confirm('Voulez vous supprimer définitivement le contenu ?')">
                                <i class="fa fa-times"></i> Supprimer
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>

                    <tr class="info">
                        <td colspan="5">
                            Aucun lien dans le menu.
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5"></th>
                    </tr>
                </tfoot>
            </table>
            <?php if ($menu): ?>

                <p>Vous pouvez déplacer vos liens du menu à l'aide de votre souris.</p>
            <?php endif; ?>

        </fieldset>
        <?php if ($menu): ?>

            <?php echo $form->form_token(); ?>
            <?php echo $form->form_input('submit'); ?>
            <?php echo $form->form_close(); ?>
        <?php endif; ?>

    </div>
</div> <!-- /.row -->