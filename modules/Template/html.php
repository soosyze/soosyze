<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <title><?php echo $title ?></title>
        <meta name="description" content="<?php echo $description ?>"/>
        <meta name="keywords" content="<?php echo $keyboard ?>"/>
        <?php echo $styles ?>
        <?php echo $scripts ?>
    </head>
    <body>
        <?php if (isset($page_top)): ?>
            <?php echo $page_top ?>
        <?php endif; ?>
        <?php echo $block[ 'page' ] ?>
        <?php if (isset($page_bottom)): ?>
            <?php echo $page_bottom ?>
        <?php endif; ?>
    </body>
</html>