
<ol class="nestable-menu <?php echo if_or($level === 1, '', 'nestable-list'); ?>"
    data-draggable="sortable" data-group="nested-menu" data-ghostClass="placeholder" data-onEnd="sortMenu">
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
                    <?php echo if_or(!empty($link[ 'icon' ]), '<i class="' . htmlspecialchars($link[ 'icon' ]) . '" aria-hidden="true"></i> '); ?>
                        <?php echo t($link[ 'title_link' ]); ?>
                </a>
            </div>

            <div class="table-width-100">
                <input type="checkbox" name="active-<?php echo $link[ 'link_id' ]; ?>"
                       id="active-<?php echo $link[ 'link_id' ]; ?>" 
                       <?php echo if_or($link[ 'active' ], 'checked'); ?>
                    >

                <label for="active-<?php echo $link[ 'link_id' ]; ?>">
                    <span class="ui"></span> <?php echo t('Active'); ?>
                </label>
            </div>

            <div class="table-width-300">
                <a class="btn btn-action" href="<?php echo $link[ 'link_edit' ]; ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i> <?php echo t('Edit'); ?>
                </a>

                <a href="<?php echo $link[ 'link_remove' ]; ?>"
                   class="btn btn-action btn-action-remove"
                   data-toogle="modal"
                   data-target="#modal_menu">
                    <i class="fa fa-times" aria-hidden="true"></i> <?php echo t('Delete'); ?>
                </a>
            </div>
        </div>

        <input type="hidden" name="link_id-<?php echo $link[ 'link_id' ]; ?>" value="<?php echo $link[ 'link_id' ]; ?>">
        <input type="hidden" name="weight-<?php echo $link[ 'link_id' ]; ?>" value="<?php echo $link[ 'weight' ]; ?>">
        <input type="hidden" name="parent-<?php echo $link[ 'link_id' ]; ?>" value="<?php echo $link[ 'parent' ]; ?>">

        <?php echo $link[ 'submenu' ]; ?>

    </li>
    <?php endforeach; endif; ?>

</ol>
