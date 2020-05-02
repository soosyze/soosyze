
<header>
    <?php if( $logo ): ?>
        <img src="<?php echo $logo; ?>" alt="Logo site">
    <?php endif; ?>

    <h1><?php echo $title_main; ?></h1>

</header>

<div class="main-wrapper">
    <div class="main-content">
        <div class="container">
            <div class="row">
                <?php if( !empty($section[ 'messages' ]) ): ?>

                    <div class="col-md-12">
                        <?php echo $section[ 'messages' ]; ?>

                    </div>
                <?php endif; ?>

                <div class="col-md-12">
                    <?php if( !empty($section[ 'content' ]) ): ?>
                        <?php echo $section[ 'content' ]; ?>

                    <?php else: ?>

                        <p><?php echo t('The site is currently under maintenance. Thank you for your understanding.'); ?></p>
                    <?php endif; ?>
                </div>
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