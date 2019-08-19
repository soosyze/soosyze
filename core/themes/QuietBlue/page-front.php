
<header id="wrapper_header" role="banner">
    <?php if ($logo): ?>

        <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
    <?php endif; ?>

    <h1><?php echo $title; ?></h1>
</header>
<nav id="nav_main" role="navigation">
    <ul>
        <li>
            <span id="toogle_menu"><i class="fa fa-ellipsis-v" aria-hidden="true"></i> Menu</span>
            <?php echo $section[ 'main_menu' ]; ?>

        </li>
    </ul>
</nav>
<div id="wrapper_main">
    <header>
        <h2><?php echo $title_main; ?></h2>
    </header>
    <div class="container">
        <div class="row">
            <?php if (!empty($section[ 'header' ])): ?>

            <div class="col-md-12">
                <?php echo $section[ 'header' ]; ?>

            </div>
            <?php endif; ?>
            <?php if (!empty($section[ 'messages' ])): ?>

            <div class="col-md-12">
                <?php echo $section[ 'messages' ]; ?>

            </div>
            <?php endif; ?>
            <?php if (!empty($section[ 'sidebar' ])): ?>

            <div class="col-md-4">
                <?php echo $section[ 'sidebar' ]; ?>

            </div>
            <?php endif; ?>
            <?php if (!empty($section[ 'sidebar' ])): ?>

            <div class="col-md-8">
            <?php else: ?>

            <div class="col-sm-12">
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
    </div>
</div>
<footer id="wrapper_footer" role="contentinfo">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php if (!empty($section[ 'footer' ])): ?>

                    <?php echo $section[ 'footer' ]; ?>
                <?php endif; ?>

                <?php echo $section[ 'second_menu' ]; ?>

            </div>
        </div>
    </div>
</footer>