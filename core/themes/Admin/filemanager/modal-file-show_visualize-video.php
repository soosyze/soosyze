
<video width="100%" height="auto" controls>
    <source src="<?php echo $path; ?>" type="video/<?php echo $extension; ?>"/>
    <p><?php echo t('Your browser does not support the @name element', [ '@name' => 'video' ]); ?></p>
</video>