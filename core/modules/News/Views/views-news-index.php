
<div class="row">
<?php if ($news): ?>

    <div class="col-md-12">
    <?php foreach ($news as $new): ?>

        <article class="card">

            <div class="card__main">
                <div class="card__header">
                    <h3 class="card__title">
                        <a href=" <?php echo $new[ 'link_view' ]; ?>">
                            <?php echo $new[ 'title' ]; ?>
                        </a>
                    </h3>
                </div>
                <div class="card__date_tags">
                    <span class="card__date"><?php echo date(t('d F, Y'), $new[ 'date_created' ]); ?></span>
                    <?php echo $new[ 'field' ][ 'image' ]['field_display']; ?>

                </div>
                <div class="card__content">
                    <?php echo $new[ 'field' ][ 'image' ]['field_display']; ?>
                    <?php echo $new[ 'field' ][ 'summary' ]['field_display']; ?>
                </div>
                <div class="card__footer">
                    <div class="card__more">
                        <a href=" <?php echo $new[ 'link_view' ]; ?>" class="btn btn-default">
                            <?php echo t('Learn more'); ?>
                        </a>
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

</div> <!-- /.row -->