<div class="row">
    <div class="col-sm-3">
        <?php if ($user[ 'picture' ]): ?>
            <img src="<?php echo $user[ 'picture' ]; ?>" class="user-picture img-responsive" alt="Picture user">
        <?php else: ?>
            <div class="user-picture_default">
                <div class="user-picture_default_username"><?php echo strtoupper($user[ 'username' ][ 0 ]); ?></div>
            </div>
        <?php endif; ?>
        <h2 class="user-title">
            <span class="user-fullname">
                <?php if ($user[ 'name' ]): ?>
                    <?php echo $user[ 'name' ] ?>
                <?php endif; ?>
                <?php if ($user[ 'firstname' ]): ?>
                    <?php echo $user[ 'firstname' ] ?>
                <?php endif; ?>
            </span>
            <span class="user-username"><?php echo $user[ 'username' ]; ?></span>
        </h2>
        <div class="user-bio"><?php echo $user[ 'bio' ]; ?></div>
    </div>
    <div class="col-sm-9">
        <?php echo $section[ 'menu_user' ]; ?>
        <fieldset>
            <legend>Utilisateur</legend>

            <?php foreach ($roles as $role): ?>

                <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span> <?php echo $role[ 'role_label' ]; ?>
            <?php endforeach; ?>

        </fieldset>
    </div>
</div>