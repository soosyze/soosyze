
<div id="wrapper_navigation" class="front">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <nav class="nav-flex nav-flex-right menu_main">
                    <?php echo $section[ 'main_menu' ]; ?>

                    <button id="toogle_menu" class="btn" data-toogle="drawer" data-target="#drawer_menu">
                        <?php echo t('Menu'); ?> <i class="fa fa-ellipsis-v"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<header id="wrapper_header">
    <div class="container">
        <div class="row">
            <div class="header-overlay "></div>
            <div class="col-md-6 col-md-offset-3">
                <div class="header-content">
                <?php if ($logo): ?>

                    <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
                <?php endif; ?>

                    <h1><?php echo htmlspecialchars($title); ?></h1>
                <?php if (!empty($section[ 'header' ])): ?>

                    <?php echo $section[ 'header' ]; ?>
                <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</header>

<div id="drawer_menu" class="drawer drawer-right">
    <div class="drawer-dialog">
        <div class="drawer-content">

            <div class="drawer-header">
                <button class="close" data-dismiss="drawer" aria-label="Close">
                    &times;
                </button>
            </div>

            <div class="drawer-body">
                <div class="navbar-nav">
                    <?php echo $section[ 'main_menu' ]; ?>
                    <?php echo $section[ 'second_menu' ]; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<div id="wrapper_main">
    <div class="container">
        <div class="row">
        <?php if (!empty($section[ 'messages' ])): ?>

            <div class="col-md-12">
                <?php echo $section[ 'messages' ]; ?>

            </div>
        <?php endif; ?>
        <?php echo!empty($section[ 'sidebar' ])
                ? '<div class="col-md-9">'
                : '<div class="col-sm-12">'; ?>

        <?php if (!empty($section[ 'content_header' ])): ?>

            <?php echo $section[ 'content_header' ]; ?>
        <?php endif; ?>

       <?php echo $section[ 'submenu' ]; ?>

        <?php echo $section[ 'content' ]; ?>
        <?php if (!empty($section[ 'content_footer' ])): ?>

            <?php echo $section[ 'content_footer' ]; ?>
        <?php endif; ?>

            <?php echo '</div>'; ?>
        <?php if (!empty($section[ 'sidebar' ])): ?>

            <div class="col-md-3">
                <?php echo $section[ 'sidebar' ]; ?>

            </div>
        <?php endif; ?>
        </div>
    </div>
</div>

<hr>

<footer id="wrapper_footer">
<?php if (!empty($section[ 'footer_first' ]) || !empty($section[ 'footer_second' ])): ?>

    <div id="pre_footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                <?php if (!empty($section[ 'footer_first' ])): ?>

                    <?php echo $section[ 'footer_first' ]; ?>
                <?php endif; ?>

                </div>
                <div class="col-md-6">
                <?php if (!empty($section[ 'footer_second' ])): ?>

                    <?php echo $section[ 'footer_second' ]; ?>
                <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
            <?php if (!empty($section[ 'footer' ])): ?>

                <?php echo $section[ 'footer' ]; ?>
            <?php endif; ?>

                <div class="menu_user">
                    <?php echo $section[ 'second_menu' ]; ?>
                </div>
            </div>
        </div>
    </div>
</footer>