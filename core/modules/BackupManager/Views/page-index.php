<div class="row">
    <div class="action_bar">
        <ul class="nav nav-tabs">
            <li>
                <a href="/?q=admin/backupmanager/do"><?php echo t('Save'); ?></a>
            </li>
            <li>
                <a href="/?q=admin/backupmanager/deleteall" onclick="return confirm('<?php echo t('Delete all the backups ?') ?>');" style="color: red"><?php echo t('Delete all') ?></a>
            </li>
        </ul>
    </div>
    <fieldset class="responsive">
        <legend><?php echo t('Backups'); ?></legend>
        <?php if (empty($backups)) : ?>
            <h4 class="text-center"><?php echo t('There is no backup yet.') ?></h4> 
        <?php else: ?>
            <table id="table-file" class="table table-hover">
                <thead>
                    <tr class="form-head">
                        <th><?php echo t('Max') . ' : ' . ($max_backups ? $max_backups : 'ꝏ') ?></th>
                        <th><?php echo t('Date'); ?></th>
                        <th><?php echo t('Size'); ?></th>
                        <th><?php echo t('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $i => $backup) : ?>
                        <tr>
                            <th>
                                #<?php echo $i + 1; ?><!--<i class="far fa-2x fa-file-archive"></i>-->
                            </th>
                            <td data-title="<?php echo t('Date'); ?>">
                                <span data-tooltip="<?php echo $backup['date']->format('Y-m-d H:i:s') ?>">
                                    <?php
                                    $diff = $backup['date']->diff(new \DateTime());
                                    echo $diff->format('%m') == 0 ?
                                            $diff->format('%d') == 0 ?
                                                    $diff->format('%h') == 0 ?
                                                            $diff->format('%i') == 0 ?
                                                                    $diff->format('%s') . t(' seconds ago') : $diff->format('%i') . t(' minutes ago') : $diff->format('%h') . t(' hours ago') : $diff->format('%d') . t(' days ago') : $diff->format('%m') . t(' months ago');
                                    ?>
                                </span> 
                            </td>
                            <td data-title="<?php echo t('Size'); ?>">   
                                <span data-tooltip="<?php echo $backup['size'] . 'o' ?>">
        <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($backup['size']); ?>
                                </span>
                            </td>
                            <td data-title="<?php echo t('Actions'); ?>">
                                <a class="btn btn-action" onclick="return confirm('<?php echo t('Restore the backup ?') ?>');" href="<?php echo $backup['restore_link'] ?>">
                                    <span data-tooltip="<?php echo t('Restore') ?>" class="badge-role" style="background-color: black; cursor: pointer">
                                        <i class="fa fa-trash-restore" aria-hidden="true"></i>
                                    </span> 
                                </a>
                                <a class="btn btn-action" target="_blank" href="<?php echo $backup['download_link'] ?>">
                                    <span data-tooltip="<?php echo t('Download') ?>" class="badge-role" style="background-color: black; cursor: pointer">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                    </span> 
                                </a>
                                <a class="btn btn-action" onclick="return confirm('<?php echo t('Delete the backup ?') ?>');" href="<?php echo $backup['delete_link'] ?>">
                                    <span data-tooltip="<?php echo t('Delete') ?>" class="badge-role" style="background-color: black; cursor: pointer">
                                        <i class="fa fa-times" aria-hidden="true"></i>
                                    </span> 
                                </a>
                            </td>
                        <tr>
    <?php endforeach; ?>
                </tbody>
            </table>
                    <?php endif; ?>
    </fieldset>
</div>