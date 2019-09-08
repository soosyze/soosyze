<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="IE=edge" http-equiv="X-UA-Compatible">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Installation SoosyzeCMS</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <style>
            body {
                background: #21244F; /* Old browsers */
                background: -moz-linear-gradient(top, #21244f 0%, #1c889e 100%); /* FF3.6-15 */
                background: -webkit-linear-gradient(top, #21244f 0%,#1c889e 100%); /* Chrome10-25,Safari5.1-6 */
                background: linear-gradient(to bottom, #21244f 0%,#1c889e 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
                filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#21244f', endColorstr='#1c889e',GradientType=0 ); /* IE6-9 */
                background-attachment: fixed;
            }
            body,
            legend {
                color:#FFF;
            }
            img {
                width: 100%;
                height: auto;
            }
            .container{
                padding: 3em;
            }
            .cadre {
                padding: 2em;
                padding-top: 4em;
                padding-bottom: 4em;
                -webkit-box-shadow: 0 0 20px 0 #fff;
                -moz-box-shadow: 0 0 20px 0 #fff;
                box-shadow: 0 0 20px 0 #fff;
            }
            .form-required {
                color:red;
            }
            a.footer {
                color: #FFF;
                text-decoration: underline;
            }
            .logo-name h1 {
                margin-top: 0;
            }
            .logo-name img {
                float: left;
                width: 40px;
            }
            .profil-item {
                text-align: center;
            }
            .profil-item input[type=radio]:checked + img {
                -webkit-box-shadow: 0 0 10px 1px #fff;
                -moz-box-shadow: 0 0 10px 1px #fff;
                box-shadow: 0 0 10px 1px #fff;
            }
            .profil-item input[type=radio] + img {
                cursor: pointer;
            }
            .step {
                padding: 1em;
            }
            .step.active {
                background-color: #fff;
                color: #21244f;
            }
            .step-number {
                font-size: 1.2em;
                font-weight: 700;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="logo-name">
                        <img alt="logo" src="logo.svg">
                        <h1>Soosyze</h1>
                        <hr>
                    </div>
                    <nav>
                        <ul class="nav nav-pills nav-stacked">
                            <?php $i = 1; ?>
                            <?php foreach ($steps as $key => $step): ?>
                                <li class="step <?php
                                echo $key === $step_active
                                    ? 'active'
                                    : '';
                                ?>">
                                    <span class="step-number"><?php echo $i++; ?>.</span>
                                    <span class="step-title"><?php echo $step[ 'title' ]; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </nav>
                </div>
                <div class="col-md-6">
                    <div class="cadre">
                        <?php if (!empty($section[ 'messages' ])): ?>
                            <?php echo $section[ 'messages' ]; ?>
                        <?php endif; ?>
                        <?php echo $section[ 'page' ]; ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <br>
                    <p>Power by <strong><a class="footer" href="https://soosyze.com">SoosyzeCMS</a></strong></p>
                </div>
            </div>
        </div>
    </body>
</html>