
<div class="row">
<?php if ($nodes): ?>
    <?php foreach ($nodes as $key => $node): ?>
    <?php $node[ 'field' ] = unserialize($node[ 'field' ]); ?>

    <article class="col-md-6">
        <div class="card">
            <header class="card__header">
                <div class="card__date_tags">
                    <span class="card__date"><?php echo date('F d, Y', $node[ 'created' ]); ?></span>
                </div>
                <h3 class="card__title"><a href="<?php echo $node[ 'link_view' ]; ?>"><?php echo $node[ 'title' ]; ?></a></h3>
            </header>
            <div class="card__main">
                <div class="card__content"><?php echo $node[ 'field' ][ 'summary' ]; ?></div>
                <div class="card__footer">
                    <div class="card__more">
                        <a href="<?php echo $node[ 'link_view' ]; ?>" class="btn btn-default">En savoir plus...</a>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
<?php else: ?>

    <div class="col-md-12">
        <p><?php echo $default; ?></p>
    </div>
<?php endif; ?>

</div>