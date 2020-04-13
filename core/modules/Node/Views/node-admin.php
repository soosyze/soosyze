
<div class="action_bar">
    <a href="<?php echo $link_add; ?>" class="btn btn-primary">
        <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add content'); ?>
    </a>
</div>

<fieldset class="responsive">
    <legend><?php echo t('My contents'); ?></legend>
    <table class="table table-hover table-node">
        <thead>
            <tr class="form-head">
                <th><?php echo t('Name'); ?></th>
                <th><?php echo t('Creation date'); ?></th>
                <th class="text-right"><?php echo t('Actions'); ?></th>
                <th><?php echo t('Status'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($nodes): ?>
            <?php foreach ($nodes as $node): ?>

            <tr>
               <th>
                    <a href="<?php echo $node[ 'link_edit' ]; ?>">
                        <?php echo $node[ 'title' ]; ?>

                    </a> <small><?php echo $node[ 'type' ]; ?></small>
                </th>
                <td data-title="<?php echo t('Creation date'); ?>">
                    <?php echo strftime('%a %e %b %Y, %H:%M', $node[ 'date_created' ]); ?>

                </td>
                <td data-title="<?php echo t('Actions'); ?>" class="text-right">
                    <div class="btn-group" role="group" aria-label="action">
                        <a href=" <?php echo $node[ 'link_view' ]; ?>" class="btn btn-action" target="_blank">
                            <i class="far fa-eye" aria-hidden="true"></i> <?php echo t('View'); ?></a>
                        <a href=" <?php echo $node[ 'link_clone' ]; ?>" class="btn btn-action">
                            <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Clone'); ?></a>
                        <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                            <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?></a>
                        <a href="<?php echo $node[ 'link_delete' ]; ?>" class="btn btn-action">
                            <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?></a>
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
                <td colspan="5"><?php echo t('Your site has no content at the moment.'); ?></td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</fieldset>
<div class="col-md-12">
    <?php echo $paginate; ?>
</div>