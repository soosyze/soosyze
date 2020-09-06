
<header id="wrapper_navigation" class="page lite">
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-flex ">
                    <div class="nav-flex-left">
                        <?php if ($logo): ?>

                            <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive logo">
                        <?php endif; ?>

                        <a href="<?php echo $base_path; ?>" class="title">
                            <?php echo $title; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div id="wrapper_main">
    <div class="container">
        <div class="row">
            <?php if (!empty($section[ 'messages' ])): ?>

                <div class="col-md-12">
                    <?php echo $section[ 'messages' ]; ?>

                </div>
            <?php endif; ?>

            <div class="col-md-12">

                <h1><?php echo $title_main; ?></h1>
                <?php if (!empty($section[ 'content' ])): ?>
                    <?php echo $section[ 'content' ]; ?>

                <?php else: ?>

                    <p><?php echo t('The site is currently under maintenance. Thank you for your understanding.'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<hr>

<footer id="wrapper_footer">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
            </div>
        </div>
    </div>
</footer>