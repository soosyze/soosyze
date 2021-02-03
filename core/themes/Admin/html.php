<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title><?php echo $title ?></title>

        <?php echo $meta; ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
        <?php echo $styles; ?>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/soosyze.css">
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/styles.css">
    </head>

    <body class="<?php echo $dark; ?>">
        <?php echo $section[ 'page' ]; ?>
        <?php if (isset($section[ 'page_bottom' ])): ?>
            <?php echo $section[ 'page_bottom' ]; ?>
        <?php endif; ?>
        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: .5;" src="<?php echo $base_theme; ?>assets/files/arrow.svg" alt="Scroll to top" width="40"/>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.8.3/Sortable.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <?php echo $scripts; ?>

        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
    </body>
</html>