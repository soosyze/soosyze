
<div class="row">
    <div class="col-md-3 sticky">
        <?php echo $section['submenu']; ?>
    </div>
    <div class="col-md-9">
        <div class="nav-action">
            <div class="nav-action-left">
                <a href="<?php echo $menu_delete; ?>" 
                   class="btn btn-danger"
                   data-tooltip="<?php echo t('Delete a menu'); ?>"
                   onclick="return confirm('<?php echo t('Do you want to permanently delete the content ?'); ?>')"
                   >
                    <i class="fa fa-times" aria-hidden="true"></i>
                </a>
                <a href="<?php echo $menu_edit; ?>" class="btn btn-default" data-tooltip="<?php echo t('Edit a menu'); ?>">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
                <a href="<?php echo $menu_add; ?>" class="btn btn-default" data-tooltip="<?php echo t('Add a menu'); ?>">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
            </div>
            <div class="nav-action-right">
                <a href="<?php echo $link_add; ?>" class="btn btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a link'); ?>
                </a>
            </div>
        </div>

        <fieldset class="responsive">
            <legend><?php echo t($menuName); ?></legend>

            <?php if ($menu->getVar('menu')): ?>
                <?php echo $form->form_open(); ?>
                <?php echo $menu; ?>
                <?php echo $form->form_token('token_menu'); ?>
                <?php echo $form->form_input('submit'); ?>
                <?php echo $form->form_close(); ?>
            <?php else: ?>

            <div class="alert alert-info">
                <div class="content-nothing">
                    <i class="fa fa-inbox"></i>
                    <p><?php echo t('The menu contains no links'); ?></p>
                </div>
            </div>
            <?php endif; ?>

        </fieldset>
    </div>
</div>
