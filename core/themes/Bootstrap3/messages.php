<?php if ($errors || $warnings || $infos || $success): ?>
    <?php foreach ($errors as $value): ?>

    <div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <p><?php echo $value; ?></p>
    </div>
    <?php endforeach; ?>
    <?php foreach ($warnings as $value): ?>

    <div class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
       <p><?php echo $value; ?></p>
    </div>
    <?php endforeach; ?>
    <?php foreach ($infos as $value): ?>

    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <p><?php echo $value; ?></p>
    </div>
    <?php endforeach; ?>
    <?php foreach ($success as $value): ?>

    <div class="alert alert-success">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <p><?php echo $value; ?></p>
    </div>
    <?php endforeach; ?>
<?php endif; ?>