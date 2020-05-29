
<?php if ($news): ?>
<?php foreach ($news as $key => $new): ?>
<?php if ($key % 3 === 0): ?><div class="row"><?php endif; ?>

    <article class="col-md-4">
        <div class="card_blog">
            <header style="background-image: url('<?php echo $new[ 'field' ][ 'image' ][ 'field_value' ]; ?>');"></header>
            <div class="card_main">
                <div class="card_content">
                    <div class="card_date_tags">
                        <span class="card_date">
                            <i class="fa fa-calendar-alt"></i> 
                            <?php echo date('d.F.Y', $new[ 'date_created' ]); ?> -

                            <i class="fa fa-clock"></i> 
                            ~<?php echo $new[ 'field' ][ 'reading_time' ][ 'field_value' ] . ' ' . t('minute(s)'); ?>

                        </span>
                    </div>
                    <h3 class="card__title"><?php echo $new[ 'title' ]; ?></h3>

                    <?php echo $new[ 'field' ][ 'summary' ][ 'field_display' ]; ?>
                </div>
                <div class="card_footer">
                    <div class="card_more">
                        <a href="<?php echo $new[ 'link_view' ]; ?>" class="btn btn-default">
                            <span class="card_more_txt"><?php echo t('Learn more'); ?></span> <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </article>
<?php if ($key % 4 === 3): ?></div><?php endif; ?>
<?php endforeach; ?>
<?php else: ?>
    <p><?php echo $default; ?></p>
<?php endif; ?>
<?php if ($link_news): ?>
    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo $link_news; ?>" class="btn btn-primary"><?php echo t('Toutes les actualités'); ?></a>
        </div>
    </div>
<?php endif; ?>
