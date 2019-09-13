
<ol <?php echo $level === 1 ? 'id="main_sortable"' : ''; ?> class="nested-sortable">
<?php if ($menu): ?>
    <?php foreach ($menu as $link): ?>

    <li class="link-item">
        <div class="link-title">
            <i class="fa fa-arrows-alt" aria-hidden="true"></i>
            <a href="<?php echo $link[ 'link' ]; ?>" target="<?php echo $link[ 'target_link' ]; ?>" <?php if ($link[ 'target_link' ] === '_blank'): ?> rel="noopener noreferrer" <?php endif; ?>>
                <?php echo !empty($link['icon']) ? "<i class='{$link['icon']}' aria-hidden='true'></i> {$link[ 'title_link' ]}" : $link[ 'title_link' ]; ?>

            </a>
        </div>
        <div class="link-actions">
            <span>
                <input type="checkbox" name="active-<?php echo $link[ 'id' ]; ?>" id="active-<?php echo $link[ 'id' ]; ?>" <?php echo $link[ 'active' ] ? 'checked' : ''; ?>>
                <label for="active-<?php echo $link[ 'id' ]; ?>"><span class="ui"></span> <?php echo t('Active'); ?></label>
            </span>
            <a class="btn btn-action" href="<?php echo $link[ 'link_edit' ]; ?>">
                <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
            </a>
            <a class="btn btn-action" href="<?php echo $link[ 'link_delete' ]; ?>" onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')">
                <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
            </a>
        </div>
        <input type="hidden" name="id-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'id' ]; ?>">
        <input type="hidden" name="weight-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'weight' ]; ?>">
        <input type="hidden" name="parent-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'parent' ]; ?>">
        <?php echo $link[ 'submenu' ]; ?>

    </li>
    <?php endforeach; ?>
<?php endif; ?>

</ol>