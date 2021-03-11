
<div class="cards">
    <?php if ($news): ?>
        <?php foreach ($news as $new): ?>

            <article class="thumbnail">
                <?php if (empty($new[ 'field' ][ 'image' ][ 'field_value' ])): ?>

                    <header class="icon_default">
                        <a href="<?php echo $new[ 'link_view' ]; ?>">
                            <i class="icon <?php echo htmlspecialchars($new[ 'field' ][ 'icon' ][ 'field_value' ]); ?>"></i>
                        </a>
                    </header>
                <?php else: ?>

                    <header>
                        <a href="<?php echo $new[ 'link_view' ]; ?>">
                            <img src="<?php echo htmlspecialchars($new[ 'field' ][ 'image' ][ 'field_value' ]); ?>">
                        </a>
                    </header>
                <?php endif; ?>

                <main class="caption">
                    <?php if ($new[ 'sticky' ]): ?>

                        <p class="card__blog_sticky">
                            <i class="fa fa-thumbtack" aria-hidden="true"></i> <?php echo t('Pinned content'); ?>
                        </p>
                    <?php endif; ?>

                    <div class="card__date_tags">
                        <span class="card__date">
                            <i class="fa fa-calendar-alt"></i> 
                            <?php echo strftime('%d %B %Y', $new[ 'date_created' ]); ?>
                            -
                            <i class="fa fa-clock"></i> 
                            ~<?php echo $new[ 'field' ][ 'reading_time' ][ 'field_value' ]
                                . ' '
                                . t(if_or($new[ 'field' ][ 'reading_time' ][ 'field_value' ] === 1, 'minute', 'minutes')); ?>
                        </span>
                    </div>

                    <h3 class="card__title"><a href="<?php echo $new[ 'link_view' ]; ?>"><?php echo t($new[ 'title' ]); ?></a></h3>

                    <div class="card__text">
                        <?php echo $new[ 'field' ][ 'summary' ][ 'field_display' ]; ?>
                    </div>
                </main>
            </article>

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