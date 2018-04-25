<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <title><?php echo $title ?></title>
        <link rel="shortcut icon" type="image/png" href="favicon.ico" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo $basePath . '/' . $themes; ?>admin/styles.css">
        <?php echo $styles ?>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php if (isset($page_top)): ?>
            <?php echo $page_top ?>
        <?php endif; ?>
        <?php if (isset($block)): ?>
            <?php echo $block[ 'page' ] ?>
        <?php endif; ?>
        <?php if (isset($page_bottom)): ?>
            <?php echo $page_bottom ?>
        <?php endif; ?>
        <?php echo $scripts ?>
    </body>
</html>