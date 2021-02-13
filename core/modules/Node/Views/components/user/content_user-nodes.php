
<fieldset class="responsive">
    <legend><?php echo t('My contents'); ?></legend>
    <?php if ($link_add): ?>

    <div class="nav-flex">
        <div class="nav-flex-right">
            <a href="<?php echo $link_add; ?>" class="btn btn-primary btn-filter">
                <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add content'); ?>
            </a>
        </div>
    </div>
    <?php endif; ?>
    <?php if ($nodes): foreach ($nodes as $key => $node): ?>

    <p>
        <small class="node_type-badge" style="background-color: <?php echo $node['node_type_color']; ?>">
            <i class="<?php echo $node['node_type_icon']; ?>"></i> <?php echo t($node['node_type_name']); ?>
        </small>

        <?php if ($node[ 'sticky' ]): ?>

        <span data-tooltip="<?php echo t('Pinned content'); ?>">
            <i class="fa fa-thumbtack" aria-hidden="true"></i>
        </span>
        <?php endif; ?>

        <a href="<?php echo $node[ 'link_view' ]; ?>">
            <?php echo $node[ 'title' ]; ?>

        </a>
    </p>
    <?php endforeach; else: ?>

    <div colspan="5" class="alert alert-info">
        <div class="content-nothing">
            <i class="fa fa-inbox" aria-hidden="true"></i>
            <p><?php echo t($content_nothing); ?><p>
        </div>
    </div>
    <?php endif; ?>
</fieldset>