
<table id="table-file" class="table table-hover table-striped table-responsive file_manager-table" data-link_show="<?php echo $link_show; ?>">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th><?php echo t('Name'); ?></th>
            <th><?php echo t('Size'); ?></th>
            <th><?php echo t('Publishing date'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if ($files): foreach ($files as $key => $file): ?>

    <tr>
        <th class="dir-link_show" data-link_show="<?php echo $file[ 'link_show' ]; ?>">
            <span class="file <?php echo $file[ 'ext' ]; ?>"></span>
        </th>

        <td class="file-name" data-title="<?php echo t('Name'); ?>">
            <span class="wrapper">
                <span class="name-text">
                    <span class="inner-text"><?php echo $file[ 'name' ]; ?></span>
                    <?php echo if_or($file[ 'ext' ] !== 'dir', "<span class='ext'>{$file[ 'ext' ]}</span>"); ?>

                </span>
            </span>
        </td>
        <td data-title="<?php echo t('Size'); ?>">
            <span data-tooltip="<?php echo $file[ 'size_octet' ]; ?> octets"><?php echo $file[ 'size' ]; ?></span>
        </td>
        <td data-title="<?php echo t('Publishing date'); ?>">
            <?php echo $file[ 'time' ]; ?>

        </td>
    </tr><?php endforeach; else: ?>

    <tr>
        <td colspan="5" class="alert alert-info">
            <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('This directory does not currently contain any subdirectories.'); ?></p>
            </div>
        </td>
    </tr>
    <?php endif; ?>

    </tbody>
    <?php if ($files): ?>

    <tfoot>
        <tr>
            <td colspan="2"></td>
            <td colspan="3">
            <?php if ($profil[ 'folder_store' ] || $profil[ 'file_store' ]): ?>

                <span data-tooltip="<?php echo t('Total size / maximum data quota'); ?>">
                <?php echo $size_all; ?> / 
                <?php echo if_or(
    $profil[ 'folder_size' ],
    "{$profil[ 'folder_size' ]} Mo",
    '<i class="fa fa-infinity" aria-hidden="true"></i>'
); ?>

                </span>
            <?php else: ?>

                <span data-tooltip="Total size"><?php echo $size_all; ?></span>
            <?php endif; ?>

            </td>
        </tr>
    </tfoot>
    <?php endif; ?>

</table>
<input type="hidden" name="dir" value="<?php echo $path; ?>">
<input type="submit" name="deplace" value="<?php echo $text_deplace; ?>" class="btn btn-success">
<input type="submit" name="copy" value="<?php echo $text_copy; ?>" class="btn btn-default">