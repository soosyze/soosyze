
<div class="nav-flex">
    <div class="nav-flex-right">
        <a href="<?php echo $link_create_link; ?>" class="btn btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a link'); ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-3 sticky">
        <?php echo $list_menu_submenu; ?>

        <a href="<?php echo $link_create_menu; ?>" class="btn btn-primary">
            <i class="fa fa-plus" aria-hidden="true"></i> <?php echo t('Add a menu'); ?>
        </a>
    </div>
    <div class="col-md-9">

        <fieldset>
        <?php if ($menu->getVar('menu')): ?>

            <div class="table-row table-head">
                <div class="table-min-width-100">Titre</div>
                <div class="table-width-100">Statut</div>
                <div class="table-width-300">Actions</div>
            </div>
            <?php echo $form->form_open(); ?>
            <?php echo $form->form_input('__method'); ?>
            <?php echo $menu; ?>
            <?php echo $form->form_group('submit-group'); ?>
            <?php echo $form->form_close(); ?>

        <?php else: ?>

            <div class="alert alert-info">
                <div class="content-nothing">
                    <i class="fa fa-inbox" aria-hidden="true"></i>
                    <p><?php echo t('The menu contains no links'); ?></p>
                </div>
            </div>
        <?php endif; ?>

        </fieldset>
    </div>
</div>

<div id="modal_menu" class="modal" aria-label="<?php echo t('File actions window'); ?>">
    <div class="modal-dialog modal-lg">
        <div class="modal-content"></div>
    </div>
</div>