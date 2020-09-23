
<?php echo $node_submenu; ?>

<div class="article_img">
    <?php echo $fields[ 'image' ][ 'field_display' ]; ?>
</div>
<div class="article_date_time">
    <i class="fa fa-calendar-alt"></i> 
    <?php echo strftime('%d %B %Y', $node[ 'date_created' ]); ?>
    -
    <i class="fa fa-clock"></i> 
    ~<?php echo $fields[ 'reading_time' ][ 'field_value' ] . ' ' . ($fields[ 'reading_time' ][ 'field_value' ] === 1
        ? t('minute')
        : t('minutes')); ?>
</div>
<?php echo $fields[ 'body' ][ 'field_display' ]; ?>