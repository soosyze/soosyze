
<fieldset class="filedset-theme">
    <h2>
        <?php echo htmlspecialchars($active_theme[ 'extra' ][ 'soosyze' ][ 'title' ]); ?>
        <small><?php echo htmlspecialchars($active_theme[ 'version' ]); ?></small>
    </h2>

    <p><?php echo t($active_theme[ 'description' ]); ?></p>
    <?php if ($link_edit): ?>

    <a href="<?php echo $link_edit; ?>" class="btn btn-primary">
        <i class="fa fa-edit"></i> <?php echo t('Edit blocks'); ?>

    </a>
    <?php endif; ?>

    <a href="<?php echo $link_setting; ?>" class="btn btn-default">
        <i class="fa fa-cog"></i> <?php echo t('Settings'); ?>

    </a>
</fieldset>

<fieldset class="filedset-theme responsive">
    <legend><?php echo t('Theme available'); ?></legend>

    <?php echo $form->form_open(); ?>
    <table class="table table-hover table-striped table-responsive table-themes">
        <thead>
            <tr>
                <th><?php echo t('Theme'); ?></th>
                <th><?php echo t('Description'); ?></th>
                <th><?php echo t('Version'); ?></th>
                <th><?php echo t('Action'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if ($themes): foreach ($themes as $key => $theme): ?>

            <tr>
                <th><?php echo htmlspecialchars($theme[ 'extra' ][ 'soosyze' ][ 'title' ]); ?></th>
                <td data-title="<?php echo t('Description'); ?>">
                    <?php echo t($theme[ 'description' ]); ?>

                </td>
                <td data-title="<?php echo t('Version'); ?>">
                    <?php echo htmlspecialchars($theme[ 'version' ]); ?>

                </td>
                <td data-title="<?php echo t('Action'); ?>">
                    <?php echo $form->form_group($key); ?>
                </td>
            </tr>
        <?php endforeach; else: ?>

            <tr class="content-nothing">
                <td colspan="4" class="alert alert-info">
                    <i class="fa fa-inbox" aria-hidden="true"></i>
                    <p><?php echo t('Your site has no content at the moment.'); ?><p>
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
    <?php echo $form->form_input('token_theme'); ?>
    <?php echo $form->form_close(); ?>
</fieldset>