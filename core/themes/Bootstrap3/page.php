<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-admin" aria-expanded="false">
                Menu <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbar-admin">
            <?php echo $section[ 'main_menu' ]; ?>

        </div>
    </div>
</nav>
<div class="jumbotron">
    <div class="container">
        <?php if ($logo): ?>

            <img src="<?php echo $logo; ?>" alt="Logo site" class="img-responsive">
        <?php endif; ?>

        <h1><?php echo $title_main; ?></h1>
    </div>
</div>
<div class="container">
    <div class="row">
        <?php if( !empty($section[ 'messages' ]) ): ?>

        <div class="col-md-12">
            <?php echo $section[ 'messages' ]; ?>

        </div>
        <?php endif; ?>
        <?php if( !empty($section[ 'sidebar' ]) ): ?>

        <div class="col-md-4">
            <?php echo $section[ 'sidebar' ]; ?>

        </div>
        <?php endif; ?>

        <?php if( !empty($section[ 'sidebar' ]) ): ?>
            <?php echo '<div class="col-md-8">'; ?>
        <?php else: ?>
            <?php echo '<div class="col-sm-12">'; ?>
        <?php endif; ?>

        <?php echo $section[ 'content' ]; ?>
        <?php echo '</div>'; ?>

    </div>
    <div class="row">
        <div class="col-md-6">
            <h2>Titre Block</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sit amet ipsum dolor. 
                Nulla nec porttitor augue. Sed pretium laoreet fringilla. Morbi maximus turpis magna,
                convallis faucibus justo fermentum at. Nulla ultrices dignissim metus in varius.</p>
        </div>
        <div class="col-md-6">
            <h2>Titre Block</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sit amet ipsum dolor. 
                Nulla nec porttitor augue. Sed pretium laoreet fringilla. Morbi maximus turpis magna,
                convallis faucibus justo fermentum at. Nulla ultrices dignissim metus in varius.</p>
        </div>
    </div>
    <hr>
    <footer>
        <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
        <?php echo $section[ 'second_menu' ]; ?>

    </footer>
</div>