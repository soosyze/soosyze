
<header id="wrapper_header">
    <?php if ($logo): ?>

        <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
    <?php endif; ?>

    <h1><?php echo $title; ?></h1>
</header>

<div id="wrapper_main">
    <header>
        <h2><?php echo $title_main; ?></h2>
    </header>
    <div class="container">
        <div class="row">
            <?php if (!empty($section[ 'messages' ])): ?>

                <div class="col-md-12">
                    <?php echo $section[ 'messages' ]; ?>

                </div>
            <?php endif; ?>

            <div class="col-md-12">
                <?php if (!empty($section[ 'content' ])): ?>
                    <?php echo $section[ 'content' ]; ?>

                <?php else: ?>

                    <p><?php echo t('The site is currently under maintenance. Thank you for your understanding.'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer id="wrapper_footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
            </div>
        </div>
    </div>
</footer>