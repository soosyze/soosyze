
<header id="wrapper_header_page">
    <?php if ($logo): ?>
        <a href="<?php echo $base_path; ?>">
            <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
        </a>
    <?php endif; ?>

    <h2><a href="<?php echo $base_path; ?>"><?php echo $title; ?></a></h2>
</header>
<nav id="nav_main">
            <?php echo $section[ 'main_menu' ]; ?>

</nav>
<div id="wrapper_main">
    <header>
        <h1><?php echo $title_main; ?></h1>
    </header>
    <div class="container">
        <?php if (!empty($section[ 'messages' ])): ?>
            <?php echo $section[ 'messages' ]; ?>
        <?php endif; ?>
        <?php echo $section[ 'content' ]; ?>
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