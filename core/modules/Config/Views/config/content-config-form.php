
<div class="row">
    <?php if (!empty($form)): ?>

    <div class="col-md-3 sticky">
        <?php echo $section[ 'menu_config' ]; ?>

    </div>
    <div class="col-md-9">
        <?php echo $form ?>

    </div>
    <?php else: ?>

    <div class="col-md-12">
        <div class="alert alert-info">
            <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('No configuration available'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>