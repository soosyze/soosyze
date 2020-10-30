
<?php echo $user_manager_submenu; ?>

<div class="nav-flex">   
    <div class="nav-flex-left">
        <button class="btn btn-default" data-dismiss="toogle" data-target="#filter_user">
            <i class="fa fa-filter" aria-hidden="true"></i> <?php echo t('Filter'); ?>
        </button>
    </div>
    <div class="nav-flex-right">
        <a href="<?php echo $link_create_user; ?>" class="btn btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a user'); ?>
        </a>
    </div>
</div>

<div class="hidden filter-area" id="filter_user">
    <div class="row">
        <form action="<?php echo $link_filter_user; ?>" method="get" id="form_filter_user">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="username"><?php echo t('User name'); ?></label>
                    <input type="text" name="username" id="username" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="firstname"><?php echo t('First name'); ?></label>
                    <input type="text" name="firstname" id="firstname" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="name"><?php echo t('Name'); ?></label>
                    <input type="text" name="name" id="name" class="form-control">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="actived"><?php echo t('Status'); ?></label>
                    <select name="actived" class="form-control" id="actived">
                        <option value=""><?php echo t('All'); ?></option>
                        <option value="1"><?php echo t('Active'); ?></option>
                        <option value="0"><?php echo t('Inactive'); ?></option>
                    </select>
                </div>
            </div>
        </form>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <a href="<?php echo $link_user_admin; ?>" class="btn btn-danger"><?php echo t('Reset'); ?></a>
            </div>
        </div>
    </div>
</div>

<div class="user-table"><?php echo $section[ 'table' ]; ?></div>