<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <title><?php echo $title ?></title>
        <link rel="shortcut icon" type="image/png" href="<?php echo $themes; ?>admin/favicon.ico" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="<?php echo $themes; ?>admin/styles.css">
        <?php echo $styles ?></head>
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
        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: 0.50;" src="<?php echo $themes; ?>admin/files/arrow.png" alt="" width="40"/>
        </div>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="<?php echo $themes; ?>admin/script.js"></script>
        <?php echo $scripts ?>
    </body>
</html>