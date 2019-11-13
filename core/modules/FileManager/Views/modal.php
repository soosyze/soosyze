
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2><?php echo $title; ?></h2>
</div>
<div class="modal-messages"></div>
<div class="modal-body row">    
    <div class="col-md-12">
        <div class="actions-file">
            <?php foreach ($info[ 'actions' ] as $action): ?>
                <?php if ($action[ 'type' ] === 'button'): ?>
                    <button 
                        class="btn btn-action <?php echo $action[ 'class' ]; ?>" 
                        data-link="<?php echo $action[ 'link' ]; ?>">
                        <i class="<?php echo $action[ 'icon' ]; ?>"></i> <?php echo t($action[ 'title_link' ]); ?>
                    </button>
                <?php else: ?>
                    <a class="btn btn-action <?php echo $action[ 'class' ]; ?>"
                       href="<?php echo $action[ 'link' ]; ?>">
                        <i class="<?php echo $action[ 'icon' ]; ?>"></i> <?php echo $action[ 'title_link' ]; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php echo $form->renderForm(); ?>
    </div>
</div>