
<header>
    <nav class="navbar navbar-grey navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-admin" aria-expanded="false">
                    Menu <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbar-admin">
                <?php echo $section[ 'main_menu' ]; ?>
                <?php echo $section[ 'second_menu' ]; ?>

            </div>
        </div>
    </nav>
    <h1><?php echo $title_main; ?></h1>
    <div class="souligne" ></div>
</header>
<div class="container">
    <div class="main-wrapper">
        <?php if (!empty($section[ 'messages' ])): ?>
            <?php echo $section[ 'messages' ]; ?>
        <?php endif; ?>
        <?php echo $section[ 'content' ]; ?>

    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <footer>
                <hr>
                <p>Power by <a href="https://soosyze.com">SoosyzeCMS</a></p>
            </footer>
        </div>
    </div>
</div>