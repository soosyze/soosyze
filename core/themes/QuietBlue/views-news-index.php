
<div class="row">
<?php if ($news): ?>
    <?php foreach ($news as $new): ?>

    <article class="col-md-6">
        <div class="card">
            <header class="card__header">
                <div class="card__date_tags">
                    <span class="card__date"><?php echo date('F d, Y', $new[ 'created' ]); ?></span>
                </div>
                <h3 class="card__title"><a href="<?php echo $new[ 'link_view' ]; ?>"><?php echo $new[ 'title' ]; ?></a></h3>
            </header>
            <div class="card__main">
                <div class="card__content"><?php echo $new[ 'field' ][ 'summary' ]; ?></div>
                <div class="card__footer">
                    <div class="card__more">
                        <a href="<?php echo $new[ 'link_view' ]; ?>" class="btn btn-default">En savoir plus...</a>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
    <div class="col-md-12">
        <?php echo $paginate; ?>
    </div>
    <div class="col-md-12">
        <a type="application/rss+xml" href="<?php echo $link_rss; ?>"><i class="fa fa-rss-square" aria-hidden="true"></i></a>
    </div>
<?php else: ?>

    <div class="col-md-12">
        <p><?php echo $default; ?></p>
    </div>
<?php endif; ?>

</div>