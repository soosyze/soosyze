<div class="row">
    <div class="col-sm-12">
        <?php echo $block[ 'menu_user' ]; ?>
        <fieldset>
            <legend>Utilisateur</legend>
            <?php foreach ($roles as $role): ?>

                <span class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>"></span> <?php echo $role[ 'role_label' ]; ?>
            <?php endforeach; ?>

            <dl>
                <?php if ($user[ 'name' ]): ?>
                    <dt>Nom<dt>
                    <dd><?php echo $user[ 'name' ] ?></dd>
                <?php endif; ?>
                <?php if ($user[ 'firstname' ]): ?>
                    <dt>Prènom<dt>
                    <dd><?php echo $user[ 'firstname' ] ?></dd>
                <?php endif; ?>
            </dl>
        </fieldset>
    </div>
</div>