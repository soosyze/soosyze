
<header id="wrapper_header">
    <?php if ($logo): ?>

    <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
    <?php endif; ?>

    <h1><?php echo $title; ?></h1>
</header>
<nav id="nav_main">
    <ul>
        <li>
            <span id="toogle_menu"><i class="fa fa-ellipsis-v"></i> Menu</span>
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
            <?php if( !empty($section[ 'messages' ]) ): ?>

            <div class="col-md-12">
                <?php echo $section[ 'messages' ]; ?>

            </div>
            <?php endif; ?>
            <?php if( !empty($section[ 'sidebar' ]) ): ?>

            <div class="col-md-4">
                <?php echo $section[ 'sidebar' ]; ?>

            </div>
            <?php endif; ?>

            <?php if( !empty($section[ 'sidebar' ]) ): ?>
                <?php echo '<div class="col-md-8">'; ?>
            <?php else: ?>
                <?php echo '<div class="col-sm-12">'; ?>
            <?php endif; ?>

            <?php echo $section[ 'content' ]; ?>
            <?php echo '</div>'; ?>

        </div>
    </div>
</div>
<footer id="wrapper_footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
                <?php echo $section[ 'second_menu' ]; ?>

            </div>
        </div>
    </div>
</footer>