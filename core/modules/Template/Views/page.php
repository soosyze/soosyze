<header>
    <?php if ($logo): ?>
        <img src="<?php echo $logo; ?>" alt="Logo site">
    <?php endif; ?>
    <h1><?php echo $title_main; ?></h1>
    <?php echo $block[ 'main_menu' ]; ?>
</header>
<div class="main-wrapper">
    <div class="main-content">
        <?php echo $block[ 'messages' ]; ?>
        <div class="container">
            <?php echo $block[ 'content' ] ?>
        </div>
    </div> <!-- /.main-content -->
</div>
<footer>
    <?php echo $block[ 'second_menu' ]; ?>
</footer>