<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <title><?php echo $title ?></title>
        <meta name="description" content="<?php echo $description ?>" />
        <meta name="keywords" content="<?php echo $keyboard ?>" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <style>
            .jumbotron{
                margin-top: 51px;
            }
            h1,
            h2,
            h3{
                font-family: 'Kelly Slab','Myriad Pro', Arial, sans-serif;
                text-shadow: 1px 1px 0 #F2F2F2, 1px 2px 0 #B1B1B2;
            }
            .card{
                border-bottom: 1px dotted #bac2c9;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
            .card__content{
                margin: 15px 0;
            }
            .card__date{
                letter-spacing: 1px;
            }
        </style>
        <?php echo $styles ?>
        <?php echo $scripts ?>
    </head>
    <body>
        <?php if (isset($block)): ?>
            <?php echo $block[ 'page' ] ?>
        <?php endif; ?>
        <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    </body>
</html>