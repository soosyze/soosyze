

<div class="nav-action">
    <div class="nav-action-left">
        <button class="btn btn-default" onclick="javascript:history.back();"><?php echo t('Back'); ?></button>
    </div>
</div>

<fieldset class="responsive">
    <legend><?php echo t('Server info'); ?></legend>
    <table class="table">
        <thead>
            <tr>
                <th><?php echo t('Constante'); ?></th>
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
    <legend><?php echo t('Settings'); ?></legend>
    <table class="table">
        <thead>
            <tr>
                <th><?php echo t('Setting'); ?></th>
                <th><?php echo t('Value'); ?></th>
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