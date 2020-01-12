<div class="row">
    <div class="col-md-3 sticky">
        <form action="/?q=admin/backupmanager/do" method="post">
            <button type="submit" class="btn btn-lg btn-primary">Sauvegarder</button>
        </form>
    </div>
    <div class="col-md-9">
        <table id="table-file" class="table table-hover">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php echo t('Date'); ?></th>
                    <th><?php echo t('Size'); ?></th>
                    <th><?php echo t('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup) : ?>
                    <tr>
                        <th>
                            <i class="far fa-2x fa-file-archive"></i>
                        </th>
                        <th>   
                            <?php
                                $diff = $backup['date']->diff(new \DateTime());
                                echo $diff->format('%m')==0 ?
                                        $diff->format('%d')==0 ?
                                            $diff->format('%h')==0 ?
                                                $diff->format('%i')==0 ?
                                                    $diff->format('%s') . t(' seconds ago')
                                                : $diff->format('%i') . t(' minutes ago')
                                            : $diff->format('%h') . t(' hours ago')
                                        : $diff->format('%d') . t(' days ago')
                                    : $diff->format('%m') . t(' months ago');
                            ?>
                        </th>
                        <th>   
                            <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($backup['size']); ?>
                        </th>
                        <th>
                            <a class="btn btn-sm btn-success" onclick="return confirm('<?php echo t('Restore the backup ?') ?>');" href="<?php echo $backup['restore_link'] ?>"><?php echo t('Restore') ?></a>
                            <a class="btn btn-sm btn-danger" onclick="return confirm('<?php echo t('Delete the backup ?') ?>');" href="<?php echo $backup['delete_link'] ?>"><?php echo t('Delete') ?></a>
                        </th>
                    <tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>