
<?php echo $form->form_open(); ?>

<div class="block-list">
    <?php $i = 0; ?>
    <?php foreach ($blocks as $key => $block): ?>
        <?php echo ($i % 3 == 0) ? '<div class="row">' : '' ?>

        <div class="col-md-4">
            <div class="block-item">
                <label class="block-body">
                    <h3><?php echo $block[ 'title' ]; ?></h3>
                    <?php echo $form->form_group('radio-' . $key); ?>

                </label>
            </div>
        </div>
        <?php echo ($i % 3 == 2) ? '</div>'  : '' ?>
        <?php ++$i; ?>
    <?php endforeach; ?>

</div>
<?php echo $form->form_input($section); ?>
<?php echo $form->form_input('submit'); ?>
<?php echo $form->form_close(); ?>