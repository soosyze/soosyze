
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <div id="result-search" style="height: 2em;"></div>
            <input type="text" id="search" class="form-control" placeholder="Rechercher des modules..." onkeyup="search();" autofocus>
        </div>
        <div class="form-group">
            <input type="checkbox" id="active" onclick="search();" checked>
            <label for="active"><span class="ui"></span> Activé</label>
            <input type="checkbox" id="disabled" onclick="search();" checked>
            <label for="disabled"><span class="ui"></span> Désactivé</label>
        </div>
        <?php echo $form->form_open(); ?>
        <?php foreach ($package as $key => $modules): ?>

        <fieldset id="<?php echo $key; ?>" class="responsive">
            <legend><?php echo $key; ?></legend>
            <table class="table table-hover">
                <thead>
                    <tr class="form-head">
                        <th>(Activé) Nom</th>
                        <th>Version</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>

                    <tr id="<?php echo $module[ 'name' ]; ?>">
                        <th>
                            <?php echo $form->form_input($module[ 'name' ]); ?>
                            <?php echo $form->form_label('module-' . $module[ 'name' ]); ?>
                        </th>
                        <td data-title="Version"><?php echo $module[ 'version' ]; ?></td>
                        <td data-title="Description">
                            <?php echo $module[ 'description' ]; ?><br>
                            <?php if (!empty($module[ 'isRequired' ])): ?>

                                Requiert 
                                <span class="module-is_required">
                                    <?php echo implode(',', $module[ 'isRequired' ]); ?>

                                </span><br>
                            <?php endif; ?>
                            <?php if (!empty($module[ 'isRequiredForModule' ])): ?>

                                Est requis par 
                                <span class="module-is_required_for_module">
                                    <?php echo implode(',', $module[ 'isRequiredForModule' ]); ?>

                                </span><br>
                            <?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
            </table>
        </fieldset>
        <?php endforeach; ?>

        <?php echo $form->form_token(); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
        <?php echo $form->form_close(); ?>
    </div>
</div> <!-- /.row -->

<script>
    var packages = <?php echo json_encode($package) ?>;
    function search()
    {
        var search   = document.getElementById('search').value;
        var active   = document.getElementById('active').checked;
        var disabled = document.getElementById('disabled').checked;
        var reg      = new RegExp(search, 'i');
        var number   = 0;

        Object.keys(packages).forEach(function (modules)
        {
            var module_hide = true;
            Object.keys(packages[modules]).forEach(function (module) {
                var module_display           = document.getElementById(module);
                var checked                  = packages[modules][module].ckecked;
                module_display.style.display = "";

                /* Si l'expression régulière est correcte. */
                if (reg.test(module)) {
                    /* Si les 2 checkboxs ne sont pas cochées et que la condition ne correspond pas à l'état du module. */
                    if (!(active && disabled) && (checked !== active || checked === disabled)) {
                        module_display.style.display = "none";
                        return;
                    }
                    number++;
                    module_hide = false;
                } else {
                    module_display.style.display = "none";
                }
            });
            document.getElementById(modules).style.display = module_hide
                    ? "none"
                    : "";
        });
        document.getElementById('result-search').textContent = number + " result(s)";
    }
</script>