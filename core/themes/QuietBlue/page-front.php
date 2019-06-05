
<header id="wrapper_header">
    <?php if ($logo): ?>

        <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
    <?php endif; ?>

    <h1><?php echo $title; ?></h1>
</header>
<nav id="nav_main">
    <?php echo $block[ 'main_menu' ]; ?>
</nav>
<div id="wrapper_main">
    <header>
        <h2><?php echo $title_main; ?></h2>
    </header>
    <div class="container">
        <?php if (!empty($block[ 'messages' ])): ?>
            <?php echo $block[ 'messages' ]; ?>
        <?php endif; ?>

        <?php echo $block[ 'content' ]; ?>
    </div>
</div>
<footer id="wrapper_footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
                <?php echo $block[ 'second_menu' ]; ?>

            </div>
        </div>
    </div>
</footer>