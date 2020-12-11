
<fieldset class="responsive">
    <?php if ($count >= 1): ?>
        <legend><?php echo t($count > 1 ? ':count contents' : ':count content', [':count' => $count]); ?></legend>
    <?php endif; ?>

    <table class="table table-hover table-striped table-responsive node_manager-table">
        <thead>
            <tr class="form-head">
                <th><?php echo t('Name'); ?></th>
                <th>
                <?php if ($order_by === 'type'): ?>
                    <a href="<?php echo $link_type_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Type'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_type_sort; ?>" class="sort">
                        <?php echo t('Type'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th>
                <?php if (empty($order_by)): ?>
                    <a href="<?php echo $link_date_changed_sort; ?>" title="<?php echo t('Descending'); ?>" class="sort">
                        <?php echo t('Publishing date'); ?> <i class="fa fa-sort-amount-down" aria-hidden="true"></i>
                    </a>
                <?php elseif ($order_by === 'date_changed'): ?>
                    <a href="<?php echo $link_date_changed_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Publishing date'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_date_changed_sort; ?>" class="sort">
                        <?php echo t('Publishing date'); ?>
                    </a>
                <?php endif; ?>
                </th>
                <th class="text-right"><?php echo t('Actions'); ?></th>
                <th>
                <?php if ($order_by === 'node_status_id'): ?>
                    <a href="<?php echo $link_status_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>" class="sort">
                        <?php echo t('Status'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-sort-amount-up-alt' : 'fa-sort-amount-down'; ?>" aria-hidden="true"></i>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $link_status_sort; ?>" class="sort">
                        <?php echo t('Status'); ?>
                    </a>
                <?php endif; ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php if ($nodes): ?>
            <?php foreach ($nodes as $node): ?>

            <tr>
                <th>
                    <?php if ($node[ 'sticky' ]): ?>

                    <span data-tooltip="<?php echo t('Pinned content'); ?>">
                        <i class="fa fa-thumbtack" aria-hidden="true"></i>
                    </span>
                    <?php endif; ?>

                    <a href="<?php echo $node[ 'link_view' ]; ?>">
                        <?php echo $node[ 'title' ]; ?>

                    </a>
                </th>
                <td data-title="<?php echo t('Type'); ?>">
                    <small class="node_type-badge node_type-badge__<?php echo $node['type']; ?>">
                        <i class="<?php echo $node['node_type_icon']; ?>"></i> <?php echo t($node['node_type_name']); ?>
                    </small>
                </td>
                <td data-title="<?php echo t('Publishing date'); ?>">
                    <?php echo strftime('%a %e %b %Y, %H:%M', $node[ 'date_changed' ]); ?>

                </td>
                <td data-title="<?php echo t('Actions'); ?>" class="text-right actions-node">
                    <div class="btn-group" role="group" aria-label="action">
                        <?php if (isset($node[ 'link_edit' ])): ?>

                        <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action dropdown-item">
                            <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
                        </a>
                        <?php endif; ?>

                        <div class="dropdown">
                            <button class="btn btn-action" data-toogle="dropdown" data-target="#btn-<?php echo $key; ?>">
                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                            </button>

                            <ul id="btn-<?php echo $key; ?>" class="dropdown-menu dropdown-menu-right">
                            <?php if (isset($node[ 'link_clone' ])): ?>

                            <li>
                                <a href=" <?php echo $node[ 'link_clone' ]; ?>" class="btn btn-action dropdown-item">
                                    <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Clone'); ?>
                                </a>
                            </li>
                            <?php endif; if (isset($node[ 'link_remove' ])): ?>

                            <li>
                                <a href="<?php echo $node[ 'link_remove' ]; ?>"
                                   class="btn btn-action btn-action-remove dropdown-item"
                                   data-toogle="modal"
                                   data-target="#modal_node">
                                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </td>
                <td data-title="<?php echo t('Status'); ?>">
                    <?php if ($node[ 'node_status_id' ] === 1): ?>

                    <span class="node_status-icon node_status-icon__publish" data-tooltip="<?php echo t('Published'); ?>">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </span>
                    <?php elseif ($node[ 'node_status_id' ] === 2): ?>

                    <span class="node_status-icon node_status-icon__pending_publication" data-tooltip="<?php echo t('Pending publication'); ?>">
                        <i class="fa fa-clock" aria-hidden="true"></i>
                    </span>
                    <?php elseif ($node[ 'node_status_id' ] === 3): ?>

                    <span class="node_status-icon node_status-icon__draft" data-tooltip="<?php echo t('Draft'); ?>">
                        <i class="fa fa-pen" aria-hidden="true"></i>
                    </span>
                    <?php elseif ($node[ 'node_status_id' ] === 4): ?>

                    <span class="node_status-icon node_status-icon__archived" data-tooltip="<?php echo t('Archived'); ?>">
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
                        <p><?php
                            echo t($is_admin
                                ? 'Your site has no content at the moment.'
                                : 'No results were found for your search.');
                        ?><p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</fieldset>

<?php echo $paginate; ?>