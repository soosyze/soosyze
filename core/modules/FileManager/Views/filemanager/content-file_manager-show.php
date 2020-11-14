
<div class="col-md-12">
    <div class="dropfile-messages"></div>

    <div class="responsive">
        <div class="nav-flex">
            <div class="nav-flex-left">
                <?php echo $section[ 'breadcrumb' ]; ?>
            </div>
            <div class="nav-flex-right btn-group">
                <?php if ($granted_file_create): ?>
                    <button
                        id="file_create"
                        class="btn btn-primary"
                        data-link="<?php echo $link_file_create; ?>"
                        data-toogle="modal"
                        data-target="#modal_filemanager">
                        <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add files'); ?>

                    </button>
                <?php endif; ?>

                <button
                    id="filemanager-btn__refresh"
                    class="btn btn-primary dir-link_show"
                    data-link_show="<?php echo $link_show; ?>"
                    data-tooltip="<?php echo t('Refresh'); ?>">
                    <i class="fa fa-sync-alt" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        
        <?php echo $form; ?>

        <?php echo $section[ 'table' ]; ?>
    </div>
</div>