
<div class="nav-flex">
    <div class="nav-flex-right">
        <?php if ($delete_all_route): ?>
        <a href="<?php echo $delete_all_route ?>"
           class="btn"
           onclick="return confirm('<?php echo t('Delete all the backups ?') ?>');">
               <?php echo t('Delete all') ?>
        </a>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?php echo $do_backup_route ?>">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Create a backup'); ?>
        </a>
    </div>
</div>

<fieldset class="responsive">
    <table id="table-file" class="table table-hover table-striped table-responsive">
        <thead>
            <tr class="form-head">
                <th data-tooltip="<?php echo t('The number of backup you did and the maximum number available') ?>">
                    <i class="fa fa fa-info-circle" aria-hidden="true"></i>
                    <?php
                    echo count($backups) . ' / ' . ($max_backups
                        ? $max_backups
                        : '<i class="fa fa-infinity" aria-hidden="true"></i>')
                    ?>

                </th>
                <th><?php echo t('Date'); ?></th>
                <th><?php echo t('Size'); ?></th>
                <th><?php echo t('Actions'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($backups)): foreach ($backups as $i => $backup): ?>

            <tr>
                <th>#<?php echo $i + 1; ?></th>
                <td data-title="<?php echo t('Date'); ?>">
                    <span data-tooltip="<?php echo $backup[ 'date' ]->format('Y-m-d H:i:s') ?>">
                        <?php $date = Soosyze\Components\Util\Util::strHumansTimeDiff($backup[ 'date' ]) ?>
                        <?php echo t($date[ 0 ], [ '%s' => $date[ 1 ] ]) ?>

                    </span>
                </td>
                <td data-title="<?php echo t('Size'); ?>">
                    <span data-tooltip="<?php echo $backup[ 'size' ] ?> octets">
                        <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($backup[ 'size' ]); ?>

                    </span>
                </td>
                <td data-title="<?php echo t('Actions'); ?>">
                    <div class="btn-actions" role="group" aria-label="action">
                        <a class="btn btn-action"
                           onclick="return confirm('<?php echo t('Restore the backup ?') ?>');"
                           href="<?php echo $backup[ 'restore_link' ] ?>"
                        >
                            <i class="fa fa-trash-restore" aria-hidden="true"></i> <?php echo t('Restore') ?>
                        </a>
                        <a class="btn btn-action"
                           href="<?php echo $backup[ 'download_link' ] ?>"
                        >
                            <i class="fa fa-download" aria-hidden="true"></i> <?php echo t('Download') ?>
                        </a>
                        <a class="btn btn-action"
                           onclick="return confirm('<?php echo t('Delete the backup ?') ?>');"
                           href="<?php echo $backup[ 'delete_link' ] ?>"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete') ?>
                        </a>
                    </div>
                </td>
            <tr>
        <?php endforeach; elseif (!$is_repository): ?>

            <tr>
                <td colspan="4" class="alert alert-warning">
                    <div class="content-nothing">
                        <i class="fa fa-times" aria-hidden="true"></i>
                        <p><?php
                            echo t('The :name directory must be created to store the backups', [
                               ':name' => $name_repository
                            ]) ?></p>
                        </div>
                    </td>
            </tr>
        <?php else: ?>

            <tr>
                <td colspan="4" class="alert alert-info">
                    <div class="content-nothing">
                        <i class="fa fa-inbox" aria-hidden="true"></i>
                        <p><?php echo t('There is no backup yet') ?></p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</fieldset>
