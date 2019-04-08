
<div class="row">
    <div class="col-md-12">
        <?php echo $form->form_open(); ?>
        <?php foreach ($package as $key => $modules): ?>

        <fieldset class="responsive">
            <legend><?php echo $key; ?></legend>
            <table class="table table-hover">
                <thead>
                    <tr class="form-head">
                        <th>(Activé) Nom</th>
                        <th>Version</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($modules as $module): ?>

                <tr>
                    <th>
                        <?php echo $form->form_input($module[ 'name' ]); ?>
                        <?php echo $form->form_label('module-' . $module[ 'name' ]); ?>
                    </th>
                    <td data-title="Version"><?php echo $module[ 'version' ]; ?></td>
                    <td data-title="Description">
                        <?php echo $module[ 'description' ]; ?><br>
                        <?php if (!empty($module[ 'isRequired' ])): ?>

                        Requiert 
                        <span class="module-is_required">
                            <?php echo implode(',', $module[ 'isRequired' ]); ?>

                        </span><br>
                        <?php endif; ?>
                        <?php if (!empty($module[ 'isRequiredForModule' ])): ?>

                        Est requis par 
                        <span class="module-is_required_for_module">
                            <?php echo implode(',', $module[ 'isRequiredForModule' ]); ?>

                        </span><br>
                        <?php endif; ?>

                    </td>
                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </fieldset>
        <?php endforeach; ?>

        <?php echo $form->form_token(); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
        <?php echo $form->form_close(); ?>  
    </div>
</div> <!-- /.row -->