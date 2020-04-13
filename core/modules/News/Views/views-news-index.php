
<div class="row">
<?php if ($news): ?>

    <div class="col-md-12">
    <?php foreach ($news as $new): ?>

        <article class="col-md-6">
            <div class="card_blog">
                <header style="background-image: url('<?php echo $new[ 'field' ][ 'image' ]['field_value']; ?>');">

                </header>
                <div class="card_main">
                    <div class="card_content">
                    <div class="card_date_tags">
                        <span class="card_date">
                            <i class="fa fa-calendar-alt"></i> 
                            <?php echo date('d.F.Y', $new[ 'date_created' ]); ?>
                            -
                            <i class="fa fa-clock"></i> 
                        ~<?php echo $new[ 'field' ][ 'reading_time' ]['field_value'] . ' ' . t('minute(s)'); ?>
                        </span>
                    </div>
                    <h3 class="card__title"><?php echo $new[ 'title' ]; ?></h3>

                        <?php echo $new[ 'field' ][ 'summary' ]['field_display']; ?>
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
    <?php endforeach; ?>

    </div>
    <div class="col-md-12">
        <?php echo $paginate; ?>
    </div>
    <div class="col-md-12">
        <a type="application/rss+xml" href="<?php echo $link_rss; ?>" title="Flux RSS"><i class="fa fa-rss-square" aria-hidden="true"></i></a>
    </div>
<?php else: ?>

    <div class="col-md-12">
        <p><?php echo $default; ?></p>
    </div>
<?php endif; ?>

</div>