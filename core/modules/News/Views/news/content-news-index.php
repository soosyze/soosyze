
<div class="article_row">
    <?php if ($news): $i = 0; ?>
    <?php foreach ($news as $new): ?>

    <article class="card_blog">
        <?php if (empty($new[ 'field' ][ 'image' ]['field_value'])): ?>

        <header class="icon_default">
            <i class="<?php echo $new[ 'field' ][ 'icon' ][ 'field_value' ]; ?>" aria-hidden="true"></i>
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
                        <i class="fa fa-calendar-alt" aria-hidden="true"></i> 
                        <?php echo strftime('%d.%B.%Y', $new[ 'date_created' ]); ?>
                        -
                        <i class="fa fa-clock" aria-hidden="true"></i> 
                        ~<?php echo $new[ 'field' ][ 'reading_time' ]['field_value'] . ' ' . t('minute(s)'); ?>
                    </span>
                </div>
                <?php echo $new[ 'field' ][ 'summary' ]['field_display']; ?>
                
            </div>
        </div>

        <div class="card_footer">
            <div class="card_more">
                <a href="<?php echo $new[ 'link_view' ]; ?>" class="btn btn-default">
                    <span class="card_more_txt"><?php echo t('Learn more'); ?></span> <i class="fa fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </article>
    <?php $i++; if ($i % 3 == 0): '</div><div class="row">'; endif; ?>

    <?php endforeach; ?>

</div>

<div class="row">
    <div class="col-md-12">
        <?php echo $paginate; ?>

    </div>
    <div class="col-md-12">
        <a type="application/rss+xml" href="<?php echo $link_rss; ?>"><i class="fa fa-rss-square" aria-hidden="true" title="Flux RSS"></i></a>
    </div>
    <?php else: ?>

    <div class="col-md-12">
        <p><?php echo $default; ?></p>
    </div>
    <?php endif; ?>

</div>