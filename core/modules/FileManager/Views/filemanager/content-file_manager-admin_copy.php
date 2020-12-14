
<div class="row">
    <?php if ($filemanager): ?>
    <div class="filemanager">
        <?php echo $filemanager; ?>

    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <p><?php echo t('No preview available'); ?></p>
    </div>
    <?php endif; ?>
</div>