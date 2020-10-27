
<div class="nav-flex">
    <div class="nav-flex-right">
        <?php if ($module_update): ?>
            <div class="alert alert-info">
                <p><?php echo t('Updates are available'); ?></p>
                <p><a class="btn btn-primary" href="<?php echo $link_module_update; ?>"><?php echo t('Update your application'); ?></a></p>
            </div>
        <?php else: ?>
            <div class="nav-flex">
                <div class="nav-flex-right">
                    <a class="btn btn-primary" href="<?php echo $link_module_check; ?>" data-tooltip="<?php echo t('Last update:') . ' ' . date('d/m/Y', time()); ?>">
                        <i class="fa fa-question-circle" aria-hidden="true"></i> <?php echo t('Check for updates'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-3 sticky">
        <div class="form-group">
            <div id="result-search" style="height: 2em;"><?php echo $count; ?> modules</div>
            <input type="text" id="search" class="form-control" placeholder="<?php echo t('Search modules'); ?>" aria-label="<?php echo t('Search modules'); ?>" onkeyup="search();" autofocus>
        </div>
        <div class="form-group">
            <input type="checkbox" id="active" onclick="search();" checked>
            <label for="active"><span class="ui"></span> <?php echo t('Activated'); ?></label>
            </div>
        <div class="form-group">
            <input type="checkbox" id="disabled" onclick="search();" checked>
            <label for="disabled"><span class="ui"></span> <?php echo t('Disabled'); ?></label>
        </div>
        <nav id="nav_config">
            <ul id="top-menu" class="nav nav-pills">
                <?php foreach (array_keys($packages) as $package): ?>

                <li id="nav-<?php echo $package; ?>">
                    <a href="#<?php echo $package; ?>"><?php echo $package; ?></a>
                </li>
                <?php endforeach; ?>

            </ul>
        </nav>
    </div>
    <div class="col-md-9">
        <?php echo $form->form_open([ 'id' => 'form-package' ]); ?>
        <?php foreach ($packages as $package => $modules): ?>

        <fieldset id="<?php echo $package; ?>" class="responsive package">
            <legend><?php echo $package; ?></legend>
            <table class="table table-hover table-striped table-responsive table-modules">
                <thead>
                    <tr class="form-head">
                        <th>&nbsp;</th>
                        <th><?php echo t('Module'); ?></th>
                        <th><?php echo t('Version'); ?></th>
                        <th><?php echo t('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>

                    <tr id="<?php echo $module[ 'title' ]; ?>" class="module" data-title="<?php echo $module[ 'title' ]; ?>">
                        <td class="th">
                            <div class="module-icon" style="background-color:<?php echo $module['icon']['background-color']; ?>">
                                <i class="<?php echo $module['icon']['name']; ?>" 
                                   style="color:<?php echo $module['icon']['color']; ?>" 
                                   aria-hidden="true"></i>
                            </div>
                        </td>
                        <td data-title="<?php echo t('Module'); ?>">
                            <div class="form-group">
                            <?php echo $form->form_input("modules[{$module[ 'title' ]}]"); ?>
                            <?php echo $form->form_label($module[ 'title' ], [ 'class' => 'str-search' ]); ?>

                            </div>
                            
                            <?php echo t($module[ 'description' ]); ?>
                            <?php if (!empty($module[ 'isRequired' ])): ?>

                            <br><?php echo t('Requires'); ?> 
                            <span class="module-is_required">
                                <?php echo implode(',', $module[ 'isRequired' ]); ?>

                            </span>
                            <?php endif; ?>
                            <?php if (!empty($module[ 'isRequiredForModule' ])): ?>

                            <br><?php echo t('Is required by'); ?> 
                            <span class="module-is_required_for_module">
                                <?php echo implode(',', $module[ 'isRequiredForModule' ]); ?>

                            </span>
                            <?php endif; ?>

                        </td>
                        <td data-title="<?php echo t('Version'); ?>"><?php echo $module[ 'version' ]; ?></td>
                        <?php if (!empty($module['support'])): ?>

                        <td data-title="<?php echo t('Actions'); ?>">
                            <a class="btn btn-action" href="<?php echo $module['support']; ?>" target="_blank">
                                <i class="fas fa-question" aria-hidden="true"></i> <?php echo t('Help'); ?>
                            </a>
                        </td>
                        <?php else: ?>

                        <td></td>
                        <?php endif; ?>

                    </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </fieldset>
        <?php endforeach; ?>

        <?php echo $form->form_token('token_module_edit'); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
        <?php echo $form->form_close(); ?>

        <div class="alert alert-info" id="package-nothing" style="display:none">
            <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('No results were found for your search.'); ?></p>
            </div>
        </div>
    </div>
</div>