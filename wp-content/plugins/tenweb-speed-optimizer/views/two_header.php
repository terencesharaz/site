<?php
$two_incompatible_plugins =  \TenWebOptimizer\OptimizerAdmin::get_conflicting_plugins();
if ( !TENWEB_SO_HOSTED_ON_10WEB ) {
?>
<div class="two-header">
    <img src="<?php echo TENWEB_SO_URL; ?>/assets/images/10web_logo.svg" alt="10Web" class="two-header-img" />
</div>
<?php
}
if(!empty($two_incompatible_plugins)){
?>
<div class="two_incompatible_notice">
    <div class="two_incompatible_notice_title">Some plugins are conflicting with 10Web Booster.</div>
    <div class="two_incompatible_notice_desc">
        Deactivate these plugins so the Booster can perform website optimization as intended.
        Proceeding without deactivation can reduce the efficiency of 10Web Booster and cause technical issues.
    </div>
    <div class="two_incompatible_plugin_list">
    <?php
    foreach ($two_incompatible_plugins as $slug=>$name){
    ?>
    <div class="two_incompatible_plugins">
       <span class="two_incompatible_plugin_name"><?php echo $name; ?></span>
        <span class="two_deactivate_plugin" data-plugin-slug="<?php echo $slug ?>">Deactivate</span>
    </div>

    <?php
    };
    ?>
    </div>
</div>
<?php
};
?>
