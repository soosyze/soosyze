

<div class="nav-action">
    <div class="nav-action-left">
        <button class="btn btn-default" onclick="javascript:history.back();"><?php echo t('Retour'); ?></button>
    </div>
</div>

<fieldset class="responsive">
    <legend><?php echo t('Infos server'); ?></legend>
    <table class="table">
        <thead>
            <tr>
                <th><?php echo t('Contante'); ?></th>
                <th><?php echo t('Value'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (get_defined_constants(true)[ 'user' ] as $key => $extension): ?>
                <tr>
                    <th><?php echo $key; ?></th>
                    <td><?php echo $extension; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</fieldset>

<fieldset class="responsive">
    <legend><?php echo t('Module PHP'); ?></legend>
    <table class="table">
        <thead>
            <tr><th><?php echo t('Module'); ?></th></tr>
        </thead>
        <tbody>
            <?php
            $ext = get_loaded_extensions();
            sort($ext, SORT_FLAG_CASE | SORT_NATURAL);
            ?>
            <?php foreach ($ext as $extension): ?>
                <tr>
                    <td><?php echo $extension; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</fieldset>

<fieldset class="responsive">
    <legend><?php echo t('Paramètres'); ?></legend>
    <table class="table">
        <thead>
            <tr>
                <th><?php echo t('Paramètre'); ?></th>
                <th><?php echo t('Valeur'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $init = ini_get_all();
            ksort($init, SORT_FLAG_CASE | SORT_NATURAL);
            ?>
            <?php foreach ($init as $key => $get_all): ?>
                <tr>
                    <th><?php echo $key ?></th>
                    <td><?php echo $get_all[ 'local_value' ]; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</fieldset>