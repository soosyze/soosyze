
<fieldset>
    <legend><?php echo t('Type of content'); ?></legend>

    <div class="node_type-cards">
        <?php foreach ($node_type as $node): ?>

         <div class="node_type-card">
            <header>
                <h3 class="node_type-badge" style="background-color: <?php echo htmlspecialchars($node['node_type_color']); ?>">
                    <i class="<?php echo $node[ 'node_type_icon' ]; ?>" aria-hidden="true"></i> <?php echo t($node[ 'node_type_name' ]); ?>

                </h3>
            </header>

            <main class="node_type-content">
                <p><?php echo t($node[ 'node_type_description' ]); ?></p>
            </main>

            <footer class="node_type-footer">
                <a class="btn btn-primary" href="<?php echo $node[ 'link' ]; ?>">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                    <?php echo t('Add :name', [ ':name' => t($node[ 'node_type_name' ]) ]); ?>

                </a>
            </footer>
        </div>
        <?php endforeach; ?>

    </div>
</fieldset>
