<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <title>Installation SoosyzeCMS</title>
        <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="shortcut icon" href="favicon.png" type="image/png" />

        <style>
            .cadre{
                padding: 2em;
                padding-top: 4em;
                padding-bottom: 4em;
                -webkit-box-shadow: 0px 0px 20px 0px rgba(255,255,255,1);
                -moz-box-shadow: 0px 0px 20px 0px rgba(255,255,255,1);
                box-shadow: 0px 0px 20px 0px rgba(255,255,255,1);
            }
            body,
            legend{
                color:#FFF;
            }
            body{
                background: #21244F; /* Old browsers */
                background: -moz-linear-gradient(top, #21244f 0%, #1c889e 100%); /* FF3.6-15 */
                background: -webkit-linear-gradient(top, #21244f 0%,#1c889e 100%); /* Chrome10-25,Safari5.1-6 */
                background: linear-gradient(to bottom, #21244f 0%,#1c889e 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#21244f', endColorstr='#1c889e',GradientType=0 ); /* IE6-9 */
                background-attachment: fixed;
            }
            .form-required{
                color:red;
            }
            a.footer{
                color: #FFF;
                text-decoration: underline;
            }
            .logo-name img{
                float: left;
                width: 40px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <?php echo $block[ 'page' ] ?>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <br>
                    <p>Power by <strong><a class="footer" href="http://soosyze.com/">SoosyzeCMS</a></strong></p>
                </div>
            </div>
        </div>
    </body>
</html>