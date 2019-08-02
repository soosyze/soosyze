
<header>
    <?php if ($logo): ?>
        <img src="<?php echo $logo; ?>" alt="Logo site">
    <?php endif; ?>

    <h1><?php echo $title_main; ?></h1>
    <?php echo $section[ 'main_menu' ]; ?>

</header>
<div class="main-wrapper">
    <div class="main-content">
        <div class="container">
            <?php if (!empty($section[ 'messages' ])): ?>
                <?php echo $section[ 'messages' ]; ?>
            <?php endif; ?>
            <?php echo $section[ 'content' ]; ?>

        </div>
    </div> <!-- /.main-content -->
</div>
<footer>
    <?php echo $section[ 'second_menu' ]; ?>
</footer>