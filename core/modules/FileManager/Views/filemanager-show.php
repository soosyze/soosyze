
<div class="col-md-12">
    <div class="dropfile-messages"></div>
    <?php if ($granted_file_create): ?>
    <?php echo $form->renderForm(); ?>
    <?php endif; ?>

    <div class="responsive">
        <?php if ($granted_folder_create): ?><div class="action_bar">
            <button 
                id="folder_create"
                class="btn btn-primary" 
                data-link="<?php echo $link_add; ?>"
                data-toogle="modal" 
                data-target="#modal_folder">
                <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add folder'); ?>

            </button>
        </div>
        <?php endif; ?>
        <?php echo $section[ 'breadcrumb' ]; ?>

        <table id="table-file" class="table table-hover" data-link_show="<?php echo $link_show; ?>">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php echo t('Name'); ?></th>
                    <th><?php echo t('Size'); ?></th>
                    <th><?php echo t('Creation date'); ?></th>
                    <th><?php echo t('Actions'); ?></th>
                </tr>
                <tr>
                    <td colspan="5"><?php
                        echo t('@nb_dir folders and @nb_file files', [
                            '@nb_dir'  => $nb_dir, '@nb_file' => $nb_file ]);
                        ?>
                        <?php echo $size_all; ?>

                    </td>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $file): ?>

            <tr>
                <?php if ($file[ 'type' ] === 'dir'):?>

                <th class="<?php echo $file[ 'type' ]; ?>-link_show" data-link_show="<?php echo $file[ 'link_show' ]; ?>">
                    <span class="file <?php echo $file[ 'ext' ]; ?>"></span>
                </th>
                <?php else: ?>

                <th class="<?php echo $file[ 'type' ]; ?>-link_show" data-link_show="<?php echo $file[ 'link_show' ]; ?>" data-toogle="modal"  data-target="#modal_folder">
                    <span class="file <?php echo $file[ 'ext' ]; ?>"><i class="ext-name"><?php echo $file[ 'ext' ]; ?></i></span>
                </th>
                <?php endif; ?>
                <td class="file-name" data-title="<?php echo t('Name'); ?>">
                    <?php echo $file[ 'name' ]; ?><?php if ($file[ 'ext' ] !== 'dir'): ?><span class="ext">.<?php echo $file[ 'ext' ]; ?></span><?php endif; ?>

                </td>
                <td>
                    <span data-tooltip="<?php echo $file[ 'size_octet' ]; ?> octets"><?php echo $file[ 'size' ]; ?></span>
                </td>
                <td><?php echo $file[ 'time' ]; ?></td>
                <td class="actions-file">
                    <?php foreach ($file[ 'actions' ] as $action): if ($action[ 'type' ] === 'button'): ?><button 
                        class="btn btn-action <?php echo $action[ 'class' ]; ?>" data-link="<?php echo $action[ 'link' ]; ?>" data-tooltip="<?php echo $action[ 'title_link' ]; ?>"
                        <?php if ($action[ 'class' ] === 'mod'): ?>
                        data-toogle="modal" data-target="#modal_folder"<?php endif; ?>>
                        <i class="<?php echo $action[ 'icon' ]; ?>"></i>
                    </button>
                    <?php else: ?><a 
                        class="btn btn-action <?php echo $action[ 'class' ]; ?>" href="<?php echo $action[ 'link' ]; ?>" data-tooltip="<?php echo $action[ 'title_link' ]; ?>">
                        <i class="<?php echo $action[ 'icon' ]; ?>"></i>
                    </a>
                    <?php endif; endforeach; ?>

                </td>
            </tr><?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"><?php
                        echo t('@nb_dir folders and @nb_file files', [
                            '@nb_dir'  => $nb_dir, '@nb_file' => $nb_file ]);
                        ?>
                        <?php echo $size_all; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="modal_folder" class="modal" role="dialog" aria-label="<?php echo t('File action window.'); ?>">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Modal content -->
        <div class="modal-content"></div>
    </div>
</div>