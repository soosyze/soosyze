
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo t('Close'); ?>">&times;</button>
    <h2> <?php echo htmlspecialchars($title); ?></h2>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?php echo not_empty_or($menu); ?>

        </div>
        <div class="col-md-6">
            <div class="modal-visulaize <?php echo $type; ?>-thumbnail"><?php echo $section[ 'visualize' ]; ?></div>
        </div>
        <div class="col-md-6">
            <div class="modal-info">
                <h3><?php echo $info[ 'name' ]; ?><span class="extension">.<?php echo $info[ 'ext' ]; ?></span></h3>
                <p data-tooltip="<?php echo $info[ 'size_octet' ]; ?> octets">
                    <i class="fa fa-weight-hanging" aria-hidden="true"></i> <?php echo $info[ 'size' ]; ?>
                </p>
                <p data-tooltip="<?php echo t('Publishing date'); ?>">
                    <i class="fa fa-clock" aria-hidden="true"></i> <?php echo $info[ 'time' ]; ?>
                </p>
            </div>
        </div>
    </div>
</div>