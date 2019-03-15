
<div class="row">
    <div class="col-sm-12">
        <?php echo $form->form_open(); ?>
        <?php foreach ($package as $key => $modules): ?>

        <fieldset>
            <legend><?php echo $key; ?></legend>
            <div class="row div-thead">
                <div class="col-sm-3">(Activé) Nom</div>
                <div class="col-sm-2">Version</div>
                <div class="col-sm-7">Description</div>
            </div>
            <?php foreach ($modules as $module): ?>

            <div class="div-tbody row">
                <div class="col-sm-3">
                    <?php echo $form->form_input($module[ 'name' ]); ?>
                    <?php echo $form->form_label('module-' . $module[ 'name' ]); ?>
                </div>
                <div class="col-sm-2"><?php echo $module[ 'version' ]; ?></div>
                <div class="col-sm-7">
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

                </div>
            </div>
            <?php endforeach; ?>

        </fieldset>
        <?php endforeach; ?>

        <?php echo $form->form_token(); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
        <?php echo $form->form_close(); ?>  
    </div>
</div> <!-- /.row -->