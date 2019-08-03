
<div class="row">
    <div class="col-md-3 sticky">
        <div class="form-group">
            <div id="result-search" style="height: 2em;"><?php echo $count; ?> module(s)</div>
            <input type="text" id="search" class="form-control" placeholder="Rechercher des modules..." onkeyup="search();" autofocus>
        </div>
        <div class="form-group">
            <input type="checkbox" id="active" onclick="search();" checked>
            <label for="active"><span class="ui"></span> Activé</label>
            </div>
        <div class="form-group">
            <input type="checkbox" id="disabled" onclick="search();" checked>
            <label for="disabled"><span class="ui"></span> Désactivé</label>
        </div>
        <nav id="nav_config">
            <ul id="top-menu" class="nav nav-pills nav-stacked">
                <?php foreach (array_keys($packages) as $package): ?>

                <li id="nav-<?php echo $package; ?>">
                    <a href="#<?php echo $package; ?>"><?php echo $package; ?></a>
                </li>
                <?php endforeach; ?>

            </ul>
        </nav>
    </div>
    <div class="col-md-9">
        <?php echo $form->form_open(); ?>
        <?php foreach ($packages as $package => $modules): ?>

        <fieldset id="<?php echo $package; ?>" class="responsive package">
            <legend><?php echo $package; ?></legend>
            <table class="table table-hover table-modules">
                <thead>
                    <tr class="form-head">
                        <th></th>
                        <th>(Activé) Module</th>
                        <th>Version</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>

                    <tr id="<?php echo $module[ 'title' ]; ?>" class="module" data-title="<?php echo $module[ 'title' ]; ?>">
                        <th>
                            <div class="module-icon" style="background-color:<?php echo $module['icon']['background-color']; ?>">
                                <i class="<?php echo $module['icon']['name']; ?>" 
                                   style="color:<?php echo $module['icon']['color']; ?>" 
                                   aria-hidden="true"></i>
                            </div>
                        </th>
                        <td data-title="Module">
                            <div class="form-group">
                            <?php echo $form->form_input("modules[{$module[ 'title' ]}]"); ?>
                            <?php echo $form->form_label($module[ 'title' ]); ?>

                            </div>
                            
                            <?php echo $module[ 'description' ]; ?>
                            <?php if (!empty($module[ 'isRequired' ])): ?>

                            <br>Requiert 
                            <span class="module-is_required">
                                <?php echo implode(',', $module[ 'isRequired' ]); ?>

                            </span>
                            <?php endif; ?>
                            <?php if (!empty($module[ 'isRequiredForModule' ])): ?>

                            <br>Est requis par 
                            <span class="module-is_required_for_module">
                                <?php echo implode(',', $module[ 'isRequiredForModule' ]); ?>

                            </span>
                            <?php endif; ?>

                        </td>
                        <td data-title="Version"><?php echo $module[ 'version' ]; ?></td>
                        <?php if (!empty($module['support'])): ?>

                        <td data-title="Actions">
                            <a class="btn btn-action" href="<?php echo $module['support']; ?>" target="_blank">
                                <i class="fas fa-question" aria-hidden="true"></i> Aide
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

    </div>
</div> <!-- /.row -->