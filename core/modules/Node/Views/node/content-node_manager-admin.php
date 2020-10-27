
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

<div class="node-table"><?php echo $section[ 'table' ]; ?></div>

<div id="modal_node" class="modal" aria-label="<?php echo t('File actions window'); ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>