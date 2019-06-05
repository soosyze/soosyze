
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
            <table class="table table-hover ">
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
                                <i class="<?php echo $module['icon']['name']; ?>" style="color:<?php echo $module['icon']['color']; ?>"></i>
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
                                <i class="fas fa-question"></i> Aide
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

        <?php echo $form->form_token(); ?>
        <?php echo $form->form_input('submit', [ 'class' => 'btn btn-success' ]); ?>
        <?php echo $form->form_close(); ?>
    </div>
</div> <!-- /.row -->

<script>
    function search()
    {
        var search   = $('#search').val();
        var active   = $('#active').prop('checked');
        var disabled = $('#disabled').prop('checked');
        var reg      = new RegExp(search, 'i');
        var number   = 0;

        $('.package').each(function () {
            /* Si le package doit être affiché. */
            var package_hide = 'none';
            $(this).find('.module').each(function () {
                var checked = $(this).find('input[type=checkbox]').prop('checked');
                $(this).css('display', '');
                /* Si l'expression régulière est correcte. */
                if (reg.test($(this).data('title'))) {
                    /* Si les 2 checkboxs ne sont pas cochées et que la condition ne correspond pas à l'état du module. */
                    if (!(active && disabled) && (checked !== active || checked === disabled)) {
                        $(this).css('display', 'none');
                        return;
                    }
                    number++;
                    package_hide = '';
                } else {
                    $(this).css('display', 'none');
                }
            });
            $(this).css('display', package_hide);
            /* Pour l'affichage de la navigation. */
            $('#nav-' + this.id).css('display', package_hide);
        });
        $('#result-search').text(number + " module(s)");
    }

    $('#nav_config li a').click(function(){            
        var elemId = '#' + $(this).attr('href').split('#')[1];
        highlight(elemId);               
    });

    function highlight(elemId){
        var elem = $(elemId);
        elem.css("backgroundColor", "#fff"); // hack for Safari
        elem.animate({ backgroundColor: '#e1e4e8' }, 0);
        setTimeout(function(){$(elemId).animate({ backgroundColor: "#fff" }, 500)},300);
    }

    $(function() {
        // Cache selectors
        var topMenu = $("#top-menu");
        var topMenuHeight = topMenu.outerHeight();
        // All list items
        var menuItems = topMenu.find("a");
        // Anchors corresponding to menu items
        var scrollItems = menuItems.map(function(){
            var item = $($(this).attr("href"));
            if (item.length) { return item; }
        });

        // Bind to scroll
        $(window).scroll(function(){
            // Get container scroll position
            var fromTop = $(this).scrollTop()+topMenuHeight;
            // Get id of current scroll item
            var cur = scrollItems.map(function(){
                if ($(this).offset().top < fromTop)
                    return this;
            });
            // Get the id of the current element
            cur = cur[cur.length-1];
            var id = cur && cur.length ? cur[0].id : "";
            // Set/remove active class
            menuItems
                .parent().removeClass("active")
                .end().filter("[href='#"+id+"']").parent().addClass("active");
        });
    });

</script>