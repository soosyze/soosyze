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
    <?php echo $form->form_open() ?>
    <div class="row">
        <div class="col-sm-6">
            <?php echo $form->form_group('system-information-fieldset') ?>
            <?php echo $form->form_group('system-path-fieldset') ?>
        </div>
        <div class="col-sm-6">
            <?php echo $form->form_group('system-metadata-fieldset') ?>
        </div>
    </div> <!-- /.row -->
    <div class="row">
        <div class="col-md-12">
            <?php echo $form->form_token() ?>
            <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]) ?>
        </div>
    </div> <!-- /.row -->
    <?php echo $form->form_close() ?>
</div> <!-- /.container -->