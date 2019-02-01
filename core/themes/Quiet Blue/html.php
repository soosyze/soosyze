<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo $title ?></title>
        <link rel="shortcut icon" type="image/png" href="<?php echo $favicon; ?>"/>
        <meta name="description" content="<?php echo $description ?>"/>
        <meta name="keywords" content="<?php echo $keyboard ?>"/>
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/normalize.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/layout.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/style.css">
        
        <?php echo $styles ?>
        <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php echo $block[ 'page' ] ?>
        <?php if (isset($block[ 'page_bottom' ])): ?>
            <?php echo $block[ 'page_bottom' ] ?>
        <?php endif; ?>
        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: .7;" src="<?php echo $base_theme; ?>assets/files/arrow.png" alt="" width="40"/>
        </div>
        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
        <?php echo $scripts ?>
    </body>
</html>