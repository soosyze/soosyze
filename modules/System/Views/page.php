<header>
    <h1><?php echo $title_main; ?></h1>
</header>
<div class="main-wrapper">
    <?php if (isset($block[ 'sidebar_first' ])): ?>
        <div class="sidebar-first">
            <?php echo $block[ 'sidebar_first' ] ?>
        </div> <!-- /.sidebar-first -->
    <?php endif; ?>

    <div class="main-content">
        <?php echo $message ?>
        <div class="container">
            <?php echo $block[ 'content' ] ?>
        </div>
    </div> <!-- /.main-content -->

    <?php if (isset($block[ 'sidebar_second' ])): ?>
        <div class="sidebar-second">
            <?php echo $block[ 'sidebar_second' ] ?>
        </div> <!-- /.sidebar-second -->
    <?php endif; ?>
</div>
<footer>

</footer>