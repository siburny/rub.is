<?php if (td_util::get_option('tds_top_bar') != 'hide_top_bar') { ?>

    <div class="top-bar-style-1">
        <?php require_once('top-menu.php'); ?>
        <?php require_once('top-widget.php'); ?>
    </div>

<?php }
    require_once('td-login-modal.php');
?>