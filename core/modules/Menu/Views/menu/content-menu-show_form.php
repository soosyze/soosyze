

<ol <?php echo if_or($level === 1, ' id="main_sortable"'); ?> class="nested-sortable <?php echo if_or($level === 1, '', 'nestable-list'); ?>">
    <?php if ($menu): foreach ($menu as $link): ?>

    <li style="cursor: move">
        <div class="nestable-body table-row">
            <div class="table-width-minimum">
                <i class="fa fa-arrows-alt handle" aria-hidden="true"></i>
            </div>

            <div class="table-min-width-100">
                <a href="<?php echo $link[ 'link' ]; ?>"
                   <?php echo if_or($link[ 'target_link' ], ' target="_blank" rel="noopener noreferrer"'); ?>
                   >
                    <?php echo if_or(!empty($link[ 'icon' ]), "<i class='{$link[ 'icon' ]}' aria-hidden='true'></i> "); ?>
                        <?php echo $link[ 'title_link' ]; ?>
                </a>
            </div>

            <div class="table-width-100">
                <input type="checkbox" name="active-<?php echo $link[ 'id' ]; ?>"
                       id="active-<?php echo $link[ 'id' ]; ?>" 
                       <?php echo if_or($link[ 'active' ], 'checked'); ?>
                    >

                <label for="active-<?php echo $link[ 'id' ]; ?>">
                    <span class="ui"></span> <?php echo t('Active'); ?>
                </label>
            </div>

            <div class="table-width-300">
                <a class="btn btn-action" href="<?php echo $link[ 'link_edit' ]; ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
                </a>

                <a class="btn btn-action"
                   href="<?php echo $link[ 'link_delete' ]; ?>"
                   onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')">
                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="id-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'id' ]; ?>">
        <input type="hidden" name="weight-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'weight' ]; ?>">
        <input type="hidden" name="parent-<?php echo $link[ 'id' ]; ?>" value="<?php echo $link[ 'parent' ]; ?>">

        <?php echo $link[ 'submenu' ]; ?>

    </li>
    <?php endforeach; endif; ?>

</ol>
