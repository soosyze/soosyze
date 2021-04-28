<?php if ($is_granted_action): ?>

<div class="nav-flex">
    <div class="nav-flex-right btn-group">
        <div class="dropdown">
            <button class="btn btn-default" data-toogle="dropdown" data-target="#myDropdown" type="button">
                <?php echo t('Actions'); ?> <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            </button>

            <ul id="myDropdown" class="dropdown-menu dropdown-menu-right">
                <li>
                    <a href="<?php echo $link_trans; ?>" class="btn dropdown-item">
                        <i class="fa fa-language" aria-hidden="true"></i> <?php echo t('Update translation'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $link_cron; ?>" class="btn dropdown-item">
                        <i class="fa fa-concierge-bell" aria-hidden="true"></i> <?php echo t('Execute the cron task'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="tool-cards">
<?php if ($tools): ?>
    <?php foreach ($tools as $tool): ?>

    <div class="tool-card">
        <header class="tool-card-thumb">
            <a href="<?php echo $tool[ 'link' ]; ?>">
                <i class="<?php echo htmlspecialchars($tool[ 'icon' ][ 'name' ]); ?>" aria-hidden="true"></i>
            </a>
        </header>
        <main class="tool-card-content">
            <h3 class="tool-card-title"><?php echo t($tool[ 'title' ]); ?></h3>
            <p class="tool-card-description"><?php echo t($tool[ 'description' ]); ?></p>
        </main>
    </div>
    <?php endforeach; ?>
<?php else: ?>

    <div class="col-md-12">
        <div class="alert alert-info">
            <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('No tools available'); ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>