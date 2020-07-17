
<div class="row">
<?php if ($news): ?>
<?php foreach ($news as $key => $new): ?>
    <?php if ($limit == 1): ?>

    <article class="col-md-12">
    <?php elseif ($limit === 2): ?>

    <article class="col-md-6">
    <?php elseif ($limit === 3): ?>

    <article class="col-md-4">
    <?php elseif ($limit === 4): ?>

    <article class="col-md-3">
    <?php endif; ?>

        <div class="card_blog">
            <?php if (empty($new[ 'field' ][ 'image' ]['field_value'])): ?>

            <header class="icon_default">
                <i class="<?php echo $new[ 'field' ][ 'icon' ][ 'field_value' ]; ?>"></i>
            </header>
            <?php else: ?>

            <header style="background-image: url('<?php echo $new[ 'field' ][ 'image' ][ 'field_value' ]; ?>');"></header>
            <?php endif; ?>

            <div class="card_main">
                <div class="card_content">
                    <?php if ($new['sticky']): ?>

                    <small class="card_blog_sticky">
                        <i class="fa fa-thumbtack" aria-hidden="true"></i> <?php echo t('Pinned content'); ?>
                    </small>
                    <?php endif; ?>

                    <h3 class="card__title"><?php echo $new[ 'title' ]; ?></h3>
                    <div class="card_date_tags">
                        <span class="card_date">
                            <i class="fa fa-calendar-alt"></i> 
                            <?php echo date('d.F.Y', $new[ 'date_created' ]); ?> -

                            <i class="fa fa-clock"></i> 
                            ~<?php echo $new[ 'field' ][ 'reading_time' ][ 'field_value' ] . ' ' . t('minute(s)'); ?>

                        </span>
                    </div>
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
<?php endforeach; ?>
<?php else: ?>

    <p><?php echo t('No articles for the moment'); ?></p>
<?php endif; ?>
<?php if ($is_link_more): ?>

    <div class="col-md-12">
        <a href="<?php echo $link_more; ?>" class="btn btn-primary"><?php echo t('Toutes les actualités'); ?></a>
    </div>
<?php endif; ?>
</div>