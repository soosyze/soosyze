<div class="nav-flex">
    <div class="nav-flex-right btn-group">
        <a href="<?php echo $link_trans; ?>" class="btn btn-default">
            <i class="fa fa-language" aria-hidden="true"></i> <?php echo t('Update translation'); ?>
        </a>

        <a href="<?php echo $link_cron; ?>" class="btn btn-primary">
            <i class="fa fa-concierge-bell" aria-hidden="true"></i> <?php echo t('Execute the cron task'); ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-2">
        <div class="row">
            <div class="dashboard-link-utils">
                <a href="https://community.soosyze.com/" target="_blank">
                    <i class="fa fa-comments" aria-hidden="true"></i>
                    <div><?php echo t('Forum'); ?></div>
                </a>
            </div>
            <div class="dashboard-link-utils">
                <a href="https://soosyze.com/documentation" target="_blank">
                    <i class="fa fa-book" aria-hidden="true"></i>
                    <div><?php echo t('Documentation'); ?></div>
                </a>
            </div>
            <div class="dashboard-link-utils">
                <a href="https://soosyze.com/download/modules" target="_blank">
                    <i class="fa fa-store" aria-hidden="true"></i>
                    <div><?php echo t('Store'); ?></div>
                </a>
            </div>
            <div class="dashboard-link-utils">
                <a href="https://github.com/soosyze" target="_blank">
                    <i class="fa fa-code-branch" aria-hidden="true"></i>
                    <div><?php echo t('Source code'); ?></div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <fieldset class="responsive">
            <legend><?php echo t('CMS Info'); ?></legend>
            <table class="table table-hover table-responsive dashboard-table">
                <thead>
                    <tr>
                        <th><?php echo t('Setting'); ?></th>
                        <th><?php echo t('Value'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th><?php echo t('Soosyze version'); ?></th>
                        <td>1.0.0-beta2.3</td>
                    </tr>
                    <tr>
                        <th><?php echo t('Environment'); ?></th>
                        <td><?php
                            echo empty($config[ 'debug' ])
                                ? 'Production'
                                : 'Debug';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Data size'); ?></th>
                        <td>
                            <span data-tooltip="<?php echo $size_data; ?> octets">
                                <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($size_data); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Size of multimedia resources'); ?></th>
                        <td>
                            <span data-tooltip="<?php echo $size_file; ?> octets">
                                <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($size_file); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Backups size'); ?></th>
                        <td>
                            <span data-tooltip="<?php echo $size_backup; ?> octets">
                                <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($size_backup); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>

    <div class="col-md-5">
        <fieldset class="responsive">
            <legend><?php echo t('Server info'); ?></legend>
            <table class="table table-hover table-responsive">
                <thead>
                    <tr>
                        <th><?php echo t('Setting'); ?></th>
                        <th><?php echo t('Value'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>
                            <span>
                                <?php echo t('PHP version'); ?>
                            </span>
                        </th>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Maximum size of data received by the POST method'); ?>">
                                post_max_size <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('post_max_size'); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Maximum size in bytes that a script is allowed to allocate'); ?>">
                                memory_limit <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Maximum script execution time, in seconds'); ?>">
                                max_execution_time <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('max_execution_time'); ?> sec</td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('If you are allowed to upload files with forms'); ?>">
                                file_uploads <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                        </th>
                        <td><?php
                            echo ini_get('file_uploads')
                                ? '<i class="fa fa-check" aria-hidden="true"></i>'
                                : '<i class="fa fa-times" aria-hidden="true"></i>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('The maximum size in bytes of a file to load'); ?>">
                                upload_max_filesize <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">
                            <a href="<?php echo $link_about; ?>"><?php echo t('More information about the server'); ?></a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </fieldset>
    </div>
</div>