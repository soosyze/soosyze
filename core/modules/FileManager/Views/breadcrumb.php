
<ul id="breadcrumb" class="nav nav-pills">
    <?php foreach ($links as $link): ?>
        <li class="<?php echo $link['active']; ?>">
            <button 
                class="btn btn-breadcrumb dir-link_show" 
                data-link_show="<?php echo $link[ 'link' ]; ?>"><?php echo $link[ 'title_link' ]; ?>
            </button>
        </li>
    <?php endforeach; ?>
    <li>
        <button class="btn end">
            <i class="fa fa-greater-than" aria-hidden="true"></i>
        </button>
    </li>
</ul>