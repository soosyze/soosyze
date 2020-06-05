<div class="nav-action">
    <div class="nav-action-right">
        <a href="<?php echo $link_cron; ?>" class="btn btn-primary btn-filter-node">
            <i class="fa fa-concierge-bell" aria-hidden="true"></i> <?php echo t('Executer la tâche cron'); ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-2">
        <div class="row">
            <div class="link-utils">
                <a href="https://community.soosyze.com/" target="_blank">
                    <i class="fa fa-comments"></i>
                    <div><?php echo t('Forum'); ?></div>
                </a>
            </div>
            <div class="link-utils">
                <a href="https://soosyze.com/documentation" target="_blank">
                    <i class="fa fa-book"></i>
                    <div><?php echo t('Documentation'); ?></div>
                </a>
            </div>
            <div class="link-utils">
                <a href="https://soosyze.com/download/modules" target="_blank">
                    <i class="fa fa-store"></i>
                    <div><?php echo t('Store'); ?></div>
                </a>
            </div>
            <div class="link-utils">
                <a href="https://github.com/soosyze" target="_blank">
                    <i class="fa fa-code-branch"></i>
                    <div><?php echo t('Repo'); ?></div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <fieldset class="responsive">
            <legend><?php echo t('Infos CMS'); ?></legend>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo t('Paramètre'); ?></th>
                        <th><?php echo t('Valeur'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th><?php echo t('Version Soosyze'); ?></th>
                        <td>1.0.0-beta1.2</td>
                    </tr>
                    <tr>
                        <th><?php echo t('Mode'); ?></th>
                        <td><?php
                            echo empty($config[ 'debug' ])
                                ? t('Production')
                                : t('Debug');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Taille des données'); ?></th>
                        <td>
                            <span data-tooltip="<?php echo $size_data; ?> octets">
                                <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($size_data); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Taille des ressources multimédias'); ?></th>
                        <td>
                            <span data-tooltip="<?php echo $size_file; ?> octets">
                                <?php echo \Soosyze\Components\Util\Util::strFileSizeFormatted($size_file); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php echo t('Taille des sauvegardes'); ?></th>
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
            <legend><?php echo t('Infos server'); ?></legend>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo t('Paramètre'); ?></th>
                        <th><?php echo t('Valeur'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Taille maximale des données reçues par la méthode POST'); ?>">
                                <?php echo t('Version PHP'); ?>
                            </span>
                        </th>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Taille maximale des données reçues par la méthode POST'); ?>">
                                post_max_size <i class="fa fa-info-circle"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('post_max_size'); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Taille maximale en octets, qu\'un script est autorisé à allouer'); ?>">
                                memory_limit <i class="fa fa-info-circle"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Temps maximal d\'exécution d\'un script, en secondes'); ?>">
                                max_execution_time <i class="fa fa-info-circle"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('max_execution_time'); ?> sec</td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('Si vous êtes autorisé à charger des fichiers via les formulaires'); ?>">
                                file_uploads <i class="fa fa-info-circle"></i>
                            </span>
                        </th>
                        <td><?php
                            echo ini_get('file_uploads')
                                ? '<i class="fa fa-check"></i>'
                                : '<i class="fa fa-times"></i>';
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <span data-tooltip="<?php echo t('La taille maximale en octets d\'un fichier à charger'); ?>">
                                upload_max_filesize <i class="fa fa-info-circle"></i>
                            </span>
                        </th>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">
                            <a href="<?php echo $link_about; ?>"><?php echo t('En voir plus'); ?></a>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </fieldset>
    </div>
</div>