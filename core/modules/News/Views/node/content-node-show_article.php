
<?php if ($fields['image']['field_value']): ?>

<div class="article_img">
    <?php echo $fields[ 'image' ][ 'field_display' ]; ?>
</div>
<?php endif; ?>

<div class="article_date_time">
    <p>
    <?php if (!empty($user)): ?>
        <?php if (isset($user[ 'picture' ])): ?>
        <img alt="<?php echo htmlspecialchars($user[ 'username' ]); ?> picture"
             src="<?php echo $base_path . $user[ 'picture' ]; ?>"
             class="user-picture">
        <?php endif; ?>

        <?php echo t('By'); ?>
        <?php if (isset($user[ 'link' ])): ?>
            <a href="<?php echo $user[ 'link' ]; ?>"><?php echo htmlspecialchars($user[ 'username' ]); ?></a> - 
        <?php else: ?>
            <?php echo htmlspecialchars($user[ 'username' ]); ?> - 
        <?php endif; ?>
    <?php endif; ?>

    <i class="fa fa-calendar-alt"></i> 
    <?php echo strftime('%d %B %Y', $node[ 'date_created' ]); ?>
    -
    <i class="fa fa-clock"></i> 
    ~<?php echo $fields[ 'reading_time' ][ 'field_value' ] . ' ' . ($fields[ 'reading_time' ][ 'field_value' ] === 1
        ? t('minute')
        : t('minutes')); ?>
    </p>
</div>
<?php echo $fields[ 'body' ][ 'field_display' ]; ?>