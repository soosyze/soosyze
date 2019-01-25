<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-admin" aria-expanded="false">
                Menu <span class="glyphicon glyphicon-menu-hamburger"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbar-admin">
            <?php echo $block[ 'main_menu' ]; ?>
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
        <div class="col-md-12" >
            <?php echo $block[ 'messages' ] ?>
            <?php echo $block[ 'content' ] ?>
        </div>
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
        <p>Power by <a href="http://soosyze.com/">SoosyzeCMS</a></p>
        <?php echo $block[ 'second_menu' ]; ?>
    </footer>
</div>