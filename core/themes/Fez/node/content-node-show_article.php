
<?php if ($fields['image']['field_value']): ?>

<div class="article_img">
    <?php echo $fields[ 'image' ][ 'field_display' ]; ?>
</div>
<?php endif; ?>

<div class="article_date_time">
    <?php if (!empty($user)): ?>
        <?php if (isset($user[ 'picture' ])): ?>
            <img alt="<?php echo $user[ 'username' ]; ?> picture"
                 src="<?php echo $base_path . $user[ 'picture' ]; ?>"
                 class="user-picture">
        <?php endif; ?>

        <?php echo t('By'); ?>
        <?php if (isset($user[ 'link' ])): ?>
            <a href="<?php echo $user[ 'link' ]; ?>"><?php echo $user[ 'username' ]; ?></a> - 
        <?php else: ?>
            <?php echo $user[ 'username' ]; ?> - 
        <?php endif; ?>
    <?php endif; ?>

    <i class="fa fa-calendar-alt"></i> 
    <?php echo strftime('%d %B %Y', $node[ 'date_created' ]); ?>
    -
    <i class="fa fa-clock"></i> 
    ~<?php echo $fields[ 'reading_time' ][ 'field_value' ] . ' ' . ($fields[ 'reading_time' ][ 'field_value' ] === 1
        ? t('minute')
        : t('minutes')); ?>
</div>
<?php echo xss($fields[ 'body' ][ 'field_display' ]); ?>