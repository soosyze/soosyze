
<nav>
    <ul id="breadcrumb" class="nav nav-tabs">
        <?php foreach ($links as $link): ?>
        <li class="<?php echo $link['active']; ?> crumb">
            <a class="dir-link_show"
               data-link_show="<?php echo $link[ 'link' ]; ?>"><?php echo $link[ 'title_link' ]; ?>
            </a>
        </li>
        <?php endforeach; ?>

        <?php if ($granted_folder_create): ?>
        <li>
            <a
                id="folder_create"
                data-link="<?php echo $link_folder_create; ?>"
                data-tooltip="<?php echo t('Add folder'); ?>"
                data-toogle="modal"
                data-target="#modal_filemanager">
                <i class="fa fa-plus" aria-hidden="true"></i> 

            </a>
        </li>
        <?php endif; ?>
    </ul>
</nav>