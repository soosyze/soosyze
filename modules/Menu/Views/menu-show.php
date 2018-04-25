<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <?php if ($form->form_errors()): ?>
                <?php foreach ($form->form_errors() as $error): ?>
                    <div class="alert alert-danger">
                        <p><?php echo $error ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($form->form_success()): ?>
                <?php foreach ($form->form_success() as $success): ?>
                    <div class="alert alert-success">
                        <p><?php echo $success ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- /.col-sm-12 -->
    </div> <!-- /.row -->

    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo $linkAdd ?>" class="btn btn-primary">Ajouter un lien</a>
        </div>
        <div class="col-sm-12">
            <?php echo $form->form_open() ?>
            <fieldset>
                <legend><?php echo $menuName ?></legend>
                <table class="table">
                    <thead class="div-thead">
                        <tr class="form-head">
                            <th>Lien</th>
                            <th>Activé</th>
                            <th>Poids</th>
                            <th>Actions</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        <?php if ($menu): ?>
                            <?php foreach ($menu as $link): ?>
                                <tr class="draggable">
                                    <td>
                                        <a href="?<?php echo $link[ 'target_link' ] ?>">
                                            <?php echo $link[ 'title_link' ] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <?php echo $form->form_input("active-" . $link[ 'id' ]) ?>
                                            <label for="active-<?php echo $link[ 'id' ] ?>"><span class="ui"></span>&nbsp;</label>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $form->form_select("weight-" . $link[ 'id' ]) ?>
                                    </td>	
                                    <td>
                                        <a class="btn btn-default" href="<?php echo $link[ 'link_edit' ] ?>">
                                            <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Editer
                                        </a>
                                    </td>
                                    <td>
                                        <a class="btn btn-default" href="<?php echo $link[ 'link_delete' ] ?>" onclick="return confirm('Voulez vous supprimer définitivement le contenu ?')">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Supprimer
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
                    <p>Vous pouvez déplacer vos lien du menu à l'aide de votre souris</p>
                <?php endif; ?>
            </fieldset>
            <?php if ($menu): ?>
                <?php echo $form->form_token() ?>
                <?php echo $form->form_input('submit') ?>
                <?php echo $form->form_close() ?>
            <?php endif; ?>
        </div>
    </div> <!-- /.col-sm-12 -->
</div> <!-- /.container -->
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
<script>
                                    $(document).ready(function ()
                                    {
                                        $("#sortable").sortable({
                                            axis: "y",
                                            containment: 'table',
                                            stop: function (event, ui)
                                            {
                                                var i = 1;
                                                $('.draggable select').each(function ()
                                                {
                                                    $('option[value=' + i + ']', $(this)).prop('selected', true);
                                                    i++;
                                                });
                                            }
                                        });
                                    });
</script>
<style>
    .draggable{ cursor: move; }
    td{width:100%;background-color:#FFF;}
</style>