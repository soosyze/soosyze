<!DOCTYPE html>
<html id="<?php echo $id; ?>">
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
        <?php echo $block[ 'page' ] ?>
        <?php if (isset($block[ 'page_bottom' ])): ?>
            <?php echo $block[ 'page_bottom' ] ?>
        <?php endif; ?>
    </body>
</html>