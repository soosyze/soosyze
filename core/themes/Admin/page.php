
<div id="footer_main">
    <nav class="nav-flex nav-flex-right menu_main">
        <button id="toogle_menu" class="btn" data-toogle="drawer" data-target="#drawer_menu">
            <?php echo t('Menu'); ?> <i class="fa fa-ellipsis-v"></i>
        </button>
    </nav>
</div>

<div class="admin-wrapper">
    <div id="drawer_menu" class="drawer drawer-right">
        <div class="drawer-dialog">
            <div class="drawer-content">
                <div class="drawer-header drawer-header">
                    <img class="header_logo" src="<?php echo $base_path . '/logo.svg'; ?>" alt="Logo light mode">
                    <img class="header_logo_dark" src="<?php echo $base_path . '/logo_dark.svg'; ?>" alt="Logo dark mode">

                    <h2>Soosyze</h2>

                    <button class="close" data-dismiss="drawer" aria-label="Close">
                        &times;
                    </button>
                </div>

                <div class="drawer-body">
                    <div class="navbar-nav">
                        <ul>
                            <li>
                                <a href="/">
                                    <i class="fa fa-arrow-circle-left" aria-hidden="true"></i><?php echo t('Return site'); ?>

                                </a>
                            </li>
                        </ul>

                        <?php echo $section[ 'main_menu' ]; ?>
                        <?php echo $section[ 'second_menu' ]; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sidebar-wrapper">
        <div class="sidebar-wrapper__inner">
            <div class="sidebar">
                <div class="sidebar-header">
                    <img class="header_logo" src="<?php echo $base_path . '/logo.svg'; ?>" alt="Logo light mode">
                    <img class="header_logo_dark" src="<?php echo $base_path . '/logo_dark.svg'; ?>" alt="Logo dark mode">
                    <h2>Soosyze</h2>
                </div>
                <div class="navbar-nav">
                    <ul>
                        <li>
                            <a href="/">
                                <i class="fa fa-arrow-circle-left" aria-hidden="true"></i><?php echo t('Return site'); ?>

                            </a>
                        </li>
                    </ul>
                    <?php echo $section[ 'main_menu' ]; ?>

                    <?php if (!empty($section[ 'sidebar' ])): ?>
                        <?php echo $section[ 'sidebar' ]; ?>

                    <?php endif; ?>
                    <?php echo $section[ 'second_menu' ]; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="content">
            <header id="header_main">
                <h1><?php echo $icon; ?> <?php echo $title_main; ?></h1>
            </header>
            <?php if (!empty($section[ 'messages' ])): ?>

                <?php echo $section[ 'messages' ]; ?>
            <?php endif; ?>

            <?php if (!empty($section[ 'content_header' ])): ?>

                <?php echo $section[ 'content_header' ]; ?>
            <?php endif; ?>

            <?php echo $section[ 'content' ]; ?>

            <?php if (!empty($section[ 'content_footer' ])): ?>

                <?php echo $section[ 'content_footer' ]; ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="sidebar-wrapper sidebar-wrapper--empty">

    </div>
</div>