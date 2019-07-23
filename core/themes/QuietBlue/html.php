<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo $title; ?></title>
        <?php if ($favicon): ?>
            <link rel="shortcut icon" type="image/png" href="<?php echo $favicon; ?>"/>
        <?php endif; ?>
        <meta name="description" content="<?php echo $description; ?>"/>
        <meta name="keywords" content="<?php echo $keyboard; ?>"/>
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/normalize.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/layout.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/style.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/admin.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/menu.css">
        <?php echo $styles; ?>
    </head>
    <body>
        <?php echo $section[ 'page' ]; ?>
        <?php if (isset($section[ 'page_bottom' ])): ?>
            <?php echo $section[ 'page_bottom' ]; ?>
        <?php endif; ?>

        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: .5;" src="<?php echo $base_theme; ?>assets/files/arrow.png" alt="" width="40"/>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js" integrity="sha256-eGE6blurk5sHj+rmkfsGYeKyZx3M4bG+ZlFyA7Kns7E=" crossorigin="anonymous"></script>
        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
        <?php echo $scripts; ?>

    </body>
</html>