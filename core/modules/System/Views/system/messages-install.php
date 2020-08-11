<?php if ($errors || $warnings || $infos || $success): ?>
    <?php foreach ($errors as $value): ?>

    <div class="alert alert-danger" role="alert"><p><?php echo $value; ?></p></div>
    <?php endforeach; ?>
    <?php foreach ($warnings as $value): ?>

    <div class="alert alert-warning" role="alert"><p><?php echo $value; ?></p></div>
    <?php endforeach; ?>
    <?php foreach ($infos as $value): ?>

    <div class="alert alert-info" role="alert"><p><?php echo $value; ?></p></div>
    <?php endforeach; ?>
    <?php foreach ($success as $value): ?>

    <div class="alert alert-success" role="alert"><p><?php echo $value; ?></p></div>
    <?php endforeach; ?>
<?php endif; ?>