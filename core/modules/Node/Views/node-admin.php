
<div class="action_bar">
    <a href="<?php echo $linkAdd; ?>" class="btn btn-primary">
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
                            <a href="<?php echo $node[ 'link_edit' ]; ?>"><?php echo $node[ 'title' ]; ?></a> <small><?php echo $node[ 'type' ]; ?></small>
                        </th>
                        <td data-title="<?php echo t('Creation date'); ?>"><?php echo gmdate('d/m/Y - H:m:s', $node[ 'created' ]); ?></td>
                        <td data-title="<?php echo t('Actions'); ?>" class="text-right">
                            <div class="btn-group" role="group" aria-label="action">
                                <a href=" <?php echo $node[ 'link_view' ]; ?>" class="btn btn-action" target="_blank">
                                    <span class="fa fa-search"></span> <?php echo t('View'); ?>
                                </a>
                                <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                                    <span class="fa fa-edit"></span> <?php echo t('Edit'); ?>
                                </a>
                                <a href="<?php echo $node[ 'link_delete' ]; ?>" class="btn btn-action" onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')">
                                    <span class="fa fa-times"></span> <?php echo t('Delete'); ?>
                                </a>
                            </div>
                        </td>
                        <td data-title="<?php echo t('Status'); ?>">
                            <?php if ($node[ 'published' ]): ?>

                                <div class="icon-publish" data-tooltip="<?php echo t('Published'); ?>"></div>
                            <?php else: ?>

                                <div class="icon-notPublish" data-tooltip="<?php echo t('Not published'); ?>"></div>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>

                <tr>
                    <td colspan="5">
                        <?php echo t('Your site has no content at the moment.'); ?>
                    </td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</fieldset>