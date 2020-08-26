
<tbody>
<?php if ($nodes): ?>
    <?php foreach ($nodes as $node): ?>

    <tr>
        <th>
            <?php if (isset($node[ 'link_edit' ])): ?>

            <a href="<?php echo $node[ 'link_edit' ]; ?>">
                <?php echo $node[ 'title' ]; ?>

            </a>
            <?php else: ?>

            <?php echo $node[ 'title' ]; ?>
            <?php endif; ?>

            <div>
                <small class="node_type-badge node_type-badge__<?php echo $node['type']; ?>">
                    <i class="<?php echo $node['node_type_icon']; ?>"></i> <?php echo t($node['node_type_name']); ?>
                </small>
            </div>
        </th>
        <td data-title="<?php echo t('Creation date'); ?>">
            <?php echo utf8_encode(strftime('%a %e %b %Y, %H:%M', $node[ 'date_created' ])); ?>

        </td>
        <td data-title="<?php echo t('Actions'); ?>" class="text-right">
            <div class="btn-group" role="group" aria-label="action">
                <a href=" <?php echo $node[ 'link_view' ]; ?>" class="btn btn-action" target="_blank">
                    <i class="far fa-eye" aria-hidden="true"></i> <?php echo t('View'); ?></a>
                <?php if (isset($node[ 'link_clone' ])): ?>

                <a href=" <?php echo $node[ 'link_clone' ]; ?>" class="btn btn-action">
                    <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Clone'); ?></a>
                <?php endif; if (isset($node[ 'link_edit' ])): ?>

                <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                    <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?></a>
                <?php endif; if (isset($node[ 'link_delete' ])): ?>

                <a href="<?php echo $node[ 'link_delete' ]; ?>" 
                   class="btn btn-action" 
                   onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')">
                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?></a>
                <?php endif; ?>

            </div>
        </td>
        <td data-title="<?php echo t('Status'); ?>">
            <?php if ($node[ 'node_status_id' ] === 1): ?>

                <span class="icon-publish" data-tooltip="<?php echo t('Published'); ?>">
                    <i class="fa fa-check" aria-hidden="true"></i>
                </span>
            <?php elseif ($node[ 'node_status_id' ] === 2): ?>

                <span class="icon-pending_publication" data-tooltip="<?php echo t('Pending publication'); ?>">
                    <i class="fa fa-clock" aria-hidden="true"></i>
                </span>
            <?php elseif ($node[ 'node_status_id' ] === 3): ?>

                <span class="icon-draft" data-tooltip="<?php echo t('Draft'); ?>">
                    <i class="fa fa-pen" aria-hidden="true"></i>
                </span>
            <?php elseif ($node[ 'node_status_id' ] === 4): ?>

                <span class="icon-archived" data-tooltip="<?php echo t('Archived'); ?>">
                    <i class="fa fa-archive" aria-hidden="true"></i>
                </span>
            <?php endif; ?>

        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>

    <tr>
        <td colspan="5" class="alert alert-info">
           <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('No results were found for your search.'); ?><p>
            </div>
        </td>
    </tr>
<?php endif; ?>
</tbody>
