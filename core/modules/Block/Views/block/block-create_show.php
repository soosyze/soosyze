
<div class="block">
    <?php if ($block[ 'title' ]): ?>

    <header class="major">
        <h2><?php echo xss($block[ 'title' ]); ?></h2>
    </header>
    <?php endif; ?>
    <?php if (empty($block['content'])): ?>

    <div class="block-content-disabled">
    <?php if (!empty($block['no_content'])): ?>
        <?php echo xss($block['no_content']); ?>
    <?php else: ?>
        <?php echo t('No content available for this block'); ?>
    <?php endif; ?>
    </div>
    <?php else: ?>
        <?php echo empty($block['hook']) ? xss($block[ 'content' ]) : $block['content']; ?>
    <?php endif; ?>

</div>