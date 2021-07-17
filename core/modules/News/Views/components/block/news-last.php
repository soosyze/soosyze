
<?php if ($news): ?>
    <div class="cards">

        <?php foreach ($news as $new): ?>

            <article class="card__blog">
                <?php if (empty($new[ 'field' ][ 'image' ][ 'field_value' ])): ?>

                    <header class="icon_default">
                        <a href="<?php echo $new[ 'link_view' ]; ?>">
                            <i class="icon <?php echo htmlspecialchars($new[ 'field' ][ 'icon' ][ 'field_value' ]); ?>"></i>
                        </a>
                    </header>
                <?php else: ?>

                    <header>
                        <a href="<?php echo $new[ 'link_view' ]; ?>">
                            <?php echo xss($new[ 'field' ][ 'image' ][ 'field_display' ]); ?>
                        </a>
                    </header>
                <?php endif; ?>

                <div class="card__content">
                    <?php if ($new[ 'sticky' ]): ?>

                        <small class="card__blog_sticky">
                            <i class="fa fa-thumbtack" aria-hidden="true"></i> <?php echo t('Pinned content'); ?>
                        </small>
                    <?php endif; ?>

                    <h3 class="card__title"><a href="<?php echo $new[ 'link_view' ]; ?>"><?php echo t($new[ 'title' ]); ?></a></h3>

                    <div class="card__text">
                        <?php echo xss($new[ 'field' ][ 'summary' ][ 'field_display' ]); ?>
                    </div>
                </div>

                <div class="card__footer">
                    <div class="card__date_tags">
                        <span class="card__date">
                            <i class="fa fa-calendar-alt"></i> 
                            <?php echo strftime('%d %B, %Y', $new[ 'date_created' ]); ?>
                            -
                            <i class="fa fa-clock"></i> 
                            ~<?php echo $new[ 'field' ][ 'reading_time' ][ 'field_value' ]
                                . ' '
                                . t(if_or($new[ 'field' ][ 'reading_time' ][ 'field_value' ] === 1, 'minute', 'minutes')); ?>
                        </span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>

    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <p><?php echo t('No articles for the moment'); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if ($is_link_more): ?>

    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo $link_more; ?>" class="btn btn-primary"><?php echo t($text_more); ?></a>
        </div>
    </div>
<?php endif; ?>