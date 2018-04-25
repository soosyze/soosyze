<div class="row">
    <?php if (!empty($nodes)): ?>
        <?php foreach ($nodes as $key => $node): ?>
            <?php $node[ 'field' ] = unserialize($node[ 'field' ]); ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><?php echo $node[ 'title' ]; ?></h3>
                    </div>
                    <div class="card__main">
                        <div class="card__date_tags">
                            <span class="card__date">Le <?php echo date('Y/m/d', $node[ 'created' ]); ?></span>
                        </div>
                        <div class="card__content"><?php echo $node[ 'field' ][ 'summary' ]; ?></div>
                        <div class="card__footer">
                            <div class="card__more">
                                <a href=" <?php echo $node[ 'link_view' ] ?>" class="btn btn-default">
                                    En savoir plus...
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <p><?php echo $default; ?></p>
        </div>
    <?php endif; ?>
</div>