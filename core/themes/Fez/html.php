<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo $title; ?></title>
        <?php if ($favicon): ?>

            <link rel="shortcut icon" type="image/png" href="<?php echo $favicon; ?>"/>
        <?php endif; ?>

        <?php echo $meta; ?>

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/normalize.css">

        <?php echo $styles; ?>
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/soosyze.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/menu.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/style.css">
    </head>
    <body>
        <?php echo $section[ 'page' ]; ?>
        <?php if (isset($section[ 'page_bottom' ])): ?>
            <?php echo $section[ 'page_bottom' ]; ?>
        <?php endif; ?>

        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: .5;" src="<?php echo $base_theme; ?>assets/files/arrow.svg" alt="Scroll to top" role="button" width="40"/>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.8.3/Sortable.min.js"></script>
        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
        <?php echo $scripts; ?>

    </body>
</html>