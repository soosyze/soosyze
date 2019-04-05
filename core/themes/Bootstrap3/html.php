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
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
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
            .navbar-toggle{
                color: #FFF;
            }
            #btn_up {
                bottom: 15px;
                cursor: pointer;
                display: none;
                position: fixed;
                right: 25px;
            }
        </style>
        <link rel="stylesheet" href="<?php echo $base_theme; ?>assets/css/admin.css">
        <?php echo $styles; ?>
        <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    </head>
    <body>
        <?php echo $block[ 'page' ]; ?>
        <?php if (isset($block[ 'page_bottom' ])): ?>
            <?php echo $block[ 'page_bottom' ]; ?>
        <?php endif; ?>
        <!-- To top -->
        <div id="btn_up">
            <img style="opacity: 0.50;" src="<?php echo $base_theme; ?>assets/files/arrow.png" alt="" width="40"/>
        </div>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <script src="<?php echo $base_theme; ?>assets/js/script.js"></script>
        <?php echo $scripts; ?>

    </body>
</html>