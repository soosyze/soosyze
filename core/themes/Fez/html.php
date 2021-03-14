<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo htmlspecialchars($title); ?></title>
        <?php if ($favicon): ?>

            <link rel="shortcut icon" type="image/png" href="<?php echo $favicon; ?>"/>
        <?php endif; ?>

        <?php echo $meta; ?>
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

        <?php echo $scripts; ?>
        <?php echo $script_inline; ?>

        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
    </body>
</html>