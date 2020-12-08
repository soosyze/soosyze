
<?php echo $user_manager_submenu; ?>

<div class="row">
    <div class="col-md-3 sticky">
        <div class="form-group">
            <div id="result-search" style="height: 2em;"><?php echo $count; ?> permissions</div>
            <div class="form-group">
                <input
                    type="text"
                    id="search"
                    class="form-control"
                    placeholder="<?php echo t('Search permissions'); ?>"
                    aria-label="<?php echo t('Search permissions'); ?>"
                    onkeyup="searchPermission();"
                    autofocus>
            </div>
        </div>

        <nav id="nav_config">
            <ul id="top-menu" class="nav nav-pills">
                <?php foreach (array_keys($modules) as $module): ?>

                <li id="nav-<?php echo \Soosyze\Components\Util\Util::strSlug($module); ?>">
                    <a href="#<?php echo \Soosyze\Components\Util\Util::strSlug($module); ?>"><?php echo $module; ?></a>
                </li>
                <?php endforeach; ?>

            </ul>
        </nav>
    </div>

    <div class="col-md-9">
        <form method="post" action="<?php echo $link_update ?>" id="form-permission">

            <?php foreach ($modules as $key => $module): ?>
            <fieldset id="<?php echo \Soosyze\Components\Util\Util::strSlug($key); ?>" class="modules responsive">
                <legend><?php echo t($key); ?></legend>
                <table class="table table-hover table-striped table-responsive permission-table">
                    <thead>
                        <tr class="form-head">
                            <th><?php echo t('Name'); ?></th>
                            <?php foreach ($roles as $key => $role): ?>

                            <th>
                                <div class="badge-role" style="background-color: <?php echo $role[ 'role_color' ]; ?>">
                                    <i class="<?php echo $role['role_icon']; ?>" aria-hidden="true"></i>
                                </div>
                                <div><?php echo t($role[ 'role_label' ]); ?></div>

                            </th>
                            <?php endforeach; ?>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($module as $key => $permission): ?>

                        <tr id="<?php echo $key ?>" class="permission"  data-title="<?php echo t($permission[ 'name' ]); ?>">
                            <th class="str-search"><?php echo t($permission[ 'name' ]); ?></th>
                            <?php foreach ($permission[ 'roles' ] as $role => $checked): ?>
                            <?php $name = $role . '[' . $key . ']' ?>

                            <td data-title="<?php echo t($roles[ $role - 1 ][ 'role_label' ]); ?>">
                                <input type="checkbox" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $key ?>" <?php echo $checked ?> aria-labelledby="role-<?php echo $role; ?>">
                                <label for="<?php echo $name ?>"><i class="ui" aria-hidden="true"></i></label>
                            </td>
                            <?php endforeach; ?>

                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </fieldset>
            <?php endforeach; ?>

            <input type="submit" name="submit" class="btn btn-success" value="<?php echo t('Save'); ?>">
        </form>

        <div class="alert alert-info" id="permission-nothing" style="display:none">
            <div class="content-nothing">
                <i class="fa fa-inbox" aria-hidden="true"></i>
                <p><?php echo t('No results were found for your search.'); ?></p>
            </div>
        </div>
    </div>
</div>
<script>
    function searchPermission()
    {
        const search = $('#search').val();
        const reg = new RegExp(search, 'i');
        let number = 0;

        $('.modules').each(function () {
            var package_hide = 'none';

            $(this).find('.permission').each(function () {
                $(this).css('display', '');

                if (reg.test($(this).data('title'))) {
                    const str = strHighlight(search, $(this).data('title'));
                    $(this).find('.str-search').html(str);

                    number++;
                    package_hide = '';
                } else {
                    $(this).css('display', 'none');
                };
            });

            $(this).css('display', package_hide);
            /* Pour l'affichage de la navigation. */
            $(`#nav-${this.id}`).css('display', package_hide);
        });

        if(number === 0) {
            $('#form-permission').css('display', 'none');
            $('#permission-nothing').css('display', '');
        } else {
            $('#form-permission').css('display', '');
            $('#permission-nothing').css('display', 'none');
        }

        $('#result-search').text(
            number <= 1
                ? `${number} permission`
                : `${number} permissions`
        );
    }
</script>