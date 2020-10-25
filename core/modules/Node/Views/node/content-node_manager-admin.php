
<div class="nav-flex">
    <div class="nav-flex-left">
        <button class="btn btn-default" onclick="document.getElementById('filter_node').classList.toggle('hidden')">
            <i class="fa fa-filter" aria-hidden="true"></i> <?php echo t('Filter'); ?>
        </button>
    </div>
    <div class="nav-flex-right">
        <?php if ($link_add): ?>
        <a href="<?php echo $link_add; ?>" class="btn btn-primary btn-filter">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add content'); ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="hidden filter-area" id="filter_node">
    <div class="row">
        <form action="<?php echo $action_filter; ?>" method="get" id="form_filter_node">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="title"><?php echo t('Title'); ?></label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control"
                        placeholder="<?php echo t('Search'); ?>">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="node_type"><?php echo t('Type of content'); ?></label>
                    <select 
                        name="types[]"
                        class="form-control select-ajax-multiple"
                        id="node_type"
                        multiple="multiple"
                        data-link="<?php echo $link_search_node_type; ?>">
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="node_status_id"><?php echo t('Status'); ?></label>
                    <select 
                        name="node_status_id[]"
                        class="form-control select-ajax-multiple"
                        id="node_status_id"
                        multiple="multiple"
                        data-link="<?php echo $link_search_status; ?>">
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <a href="<?php echo $link_index; ?>" class="btn btn-danger"><?php echo t('Reset'); ?></a>
            </div>
        </div>
    </div>
</div>

<fieldset class="responsive">
    <table class="table table-hover table-striped table-responsive node_manager-table">
        <thead>
            <tr class="form-head">
                <th><?php echo t('Name'); ?></th>
                <th>
                    <?php if (empty($order_by)): ?>
                        <a href="<?php echo $link_date_changed_sort; ?>" title="<?php echo t('Ascending'); ?>">
                            <?php echo t('Creation date'); ?> <i class="fa fa-arrow-up" aria-hidden="true"></i>
                        </a>
                    <?php elseif ($order_by === 'date_changed'): ?>
                        <a href="<?php echo $link_date_changed_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>">
                            <?php echo t('Creation date'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-arrow-up' : 'fa fa-arrow-down'; ?>" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $link_date_changed_sort; ?>" title="<?php echo t('Ascending'); ?>">
                            <?php echo t('Creation date'); ?>
                        </a>
                    <?php endif; ?>
                </th>
                <th class="text-right"><?php echo t('Actions'); ?></th>
                <th>
                    <?php if ($order_by === 'node_status_id'): ?>
                        <a href="<?php echo $link_status_sort; ?>" title="<?php echo t($is_sort_asc ? 'Ascending' : 'Descending'); ?>">
                            <?php echo t('Status'); ?> <i class="fa <?php echo $is_sort_asc ? 'fa-arrow-up' : 'fa fa-arrow-down'; ?>" aria-hidden="true"></i>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $link_status_sort; ?>" title="<?php echo t('Ascending'); ?>">
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
                    <?php echo strftime('%a %e %b %Y, %H:%M', $node[ 'date_created' ]); ?>

                </td>
                <td data-title="<?php echo t('Actions'); ?>" class="text-right">
                    <div class="btn-actions" role="group" aria-label="action">
                        <a href=" <?php echo $node[ 'link_view' ]; ?>" class="btn btn-action" target="_blank">
                            <i class="far fa-eye" aria-hidden="true"></i> <?php echo t('View'); ?></a>

                        <?php if (isset($node[ 'link_clone' ])): ?>

                        <a href=" <?php echo $node[ 'link_clone' ]; ?>" class="btn btn-action">
                            <i class="fa fa-copy" aria-hidden="true"></i> <?php echo t('Clone'); ?></a>

                        <?php endif; ?>
                        <?php if (isset($node[ 'link_edit' ])): ?>

                        <a href=" <?php echo $node[ 'link_edit' ]; ?>" class="btn btn-action">
                            <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?></a>

                        <?php endif; ?>
                        <?php if (isset($node[ 'link_delete' ])): ?>

                        <a href="<?php echo $node[ 'link_delete' ]; ?>" 
                           class="btn btn-action" 
                           onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')">
                           <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?></a>

                        <?php endif; ?>

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
                        <i class="fa fa-inbox"></i>
                        <p><?php echo t('Your site has no content at the moment.'); ?></p>
                    </div>
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</fieldset>

<?php echo $paginate; ?>
