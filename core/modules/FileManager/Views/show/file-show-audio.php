
<audio controls="controls">
    <source src="<?php echo $path; ?>" type="audio/<?php echo $extension; ?>">
    <p><?php echo t('Your browser does not support the @name element', [ '@name' => 'audio' ]); ?></p>
</audio>