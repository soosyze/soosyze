<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-admin" aria-expanded="false">
                Menu <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbar-admin">
            <?php echo $section[ 'main_menu' ]; ?>

        </div>
    </div>
</nav>
<div class="jumbotron">
    <div class="container">
        <?php if ($logo): ?>

            <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive">
        <?php endif; ?>

            <h1><?php echo htmlspecialchars($title_main); ?></h1>
        <?php if (!empty($section[ 'header' ])): ?>
            <?php echo $section[ 'header' ]; ?>
        <?php endif; ?>
    </div>
</div>
<div class="container">
    <div class="row">
        <?php if (!empty($section[ 'sidebar' ])): ?>

            <div class="col-md-4">
                <?php echo $section[ 'sidebar' ]; ?>

            </div>
        <?php endif; ?>

        <?php if (!empty($section[ 'sidebar' ])): ?>
            <?php echo '<div class="col-md-8">'; ?>
        <?php else: ?>
            <?php echo '<div class="col-sm-12">'; ?>
        <?php endif; ?>

        <?php if (!empty($section[ 'content_header' ])): ?>
            <?php echo $section[ 'content_header' ]; ?>
        <?php endif; ?>

        <?php echo $section[ 'submenu' ]; ?>

        <?php echo $section[ 'content' ]; ?>

        <?php if (!empty($section[ 'content_footer' ])): ?>
            <?php echo $section[ 'content_footer' ]; ?>
        <?php endif; ?>
        <?php echo '</div>'; ?>

    </div>
    <div class="row">
        <?php if (!empty($section[ 'footer_column_first' ])): ?>
            <div class="col-md-6">
                <?php echo $section[ 'footer_column_first' ]; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($section[ 'footer_column_second' ])): ?>
            <div class="col-md-6">
                <?php echo $section[ 'footer_column_second' ]; ?>
            </div>
        <?php endif; ?>
    </div>
    <hr>
    <footer>
        <?php if (!empty($section[ 'footer' ])): ?>
            <?php echo $section[ 'footer' ]; ?>
        <?php endif; ?>
        <?php echo $section[ 'second_menu' ]; ?>
    </footer>
</div>