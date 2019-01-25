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
                    <div class="alert alert-succe ss">
                        <p><?php echo $success ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div> <!-- /.col-sm-12 -->
    </div> <!-- /.row -->
    <div class="row">
        <div class="col-sm-12">
            <?php echo $form->renderForm(); ?>
        </div>
    </div> <!-- /.col-sm-12 -->
</div> <!-- /.container -->