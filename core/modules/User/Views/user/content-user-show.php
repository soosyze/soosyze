<div class="row">
    <div class="col-sm-3">
        <?php if ($user[ 'picture' ]): ?>

        <img src="<?php echo $base_path . $user[ 'picture' ]; ?>" class="user-picture img-responsive" alt="Picture user">
        <?php else: ?>

        <div class="user-picture_default">
            <div class="user-picture_default_username"><?php echo strtoupper($user[ 'username' ][ 0 ]); ?></div>
        </div>
        <?php endif; ?>

        <h2 class="user-title">
            <span class="user-fullname">
            <?php if ($user[ 'name' ]): ?>
                <?php echo mb_convert_case($user[ 'name' ], MB_CASE_TITLE, 'UTF-8') ?>
            <?php endif; ?>
            <?php if ($user[ 'firstname' ]): ?>
                <?php echo mb_convert_case($user[ 'firstname' ], MB_CASE_TITLE, 'UTF-8') ?>
            <?php endif; ?>
            </span>
            <span class="user-username"><?php echo $user[ 'username' ]; ?></span>
        </h2>
        <div class="user-bio"><?php echo $user[ 'bio' ]; ?></div>
    </div>
    <div class="col-sm-9">
        <?php echo $user_submenu; ?>
        <fieldset>
            <legend><?php echo t('User'); ?></legend>

            <?php foreach ($roles as $role): ?>

            <span data-tooltip="<?php echo t($role[ 'role_label' ]); ?>" class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                <i class="<?php echo $role['role_icon']; ?>" aria-hidden="true"></i>
            </span> 
            <?php endforeach; ?>

        </fieldset>
    </div>
</div>