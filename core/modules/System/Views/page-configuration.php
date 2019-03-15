
<?php echo $form->form_open(); ?>

<div class="row">
    <div class="col-sm-6">
        <?php echo $form->form_group('system-information-fieldset'); ?>
        <?php echo $form->form_group('system-path-fieldset'); ?>
    </div>
    <div class="col-sm-6">
        <?php echo $form->form_group('system-metadata-fieldset'); ?>
    </div>
</div> <!-- /.row -->
<div class="row">
    <div class="col-md-12">
        <?php echo $form->form_token(); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
    </div>
</div> <!-- /.row -->

<?php echo $form->form_close() ?>