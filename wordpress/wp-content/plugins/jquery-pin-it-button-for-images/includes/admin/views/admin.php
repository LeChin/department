<?php

?>
<div class="wrap">

			<h2><?php _e( 'jQuery Pin It Button For Images Options', 'jpibfi' ); ?></h2>
<?php
$tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'selection_options';
$tab_found = false;
foreach( $settings_tabs as $tab_name => $tab_settings)
	$tab_found = $tab_found || ( $tab_name == $tab );

$tab = false == $tab_found ? 'selection_options' : $tab;
$current_settings = $settings_tabs[ $tab ];
//TODO dokończyć pętlę
?>
<div id="icon-plugins" class="icon32"></div>
<h2 class="nav-tab-wrapper">
	<?php foreach( $settings_tabs as $tab_name => $settings) { ?>
		<a href="?page=jpibfi_settings&tab=<?php echo $tab_name; ?>" class="nav-tab <?php echo $tab_name == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $settings['tab_label']; ?></a>
	<?php } ?>
</h2>

<p>
	<a href="http://mrsztuczkens.me/jquery-pin-it-button-for-images-customization/" class="button" target="_blank" rel="nofollow"><b><?php _e( 'Plugin Customization', 'jpibfi' ); ?></b></a>
	<a href="http://mrsztuczkens.me/jquery-pin-it-button-for-images-extensions/" class="button" target="_blank" rel="nofollow"><b><?php _e( 'Plugin Extensions', 'jpibfi' ); ?></b></a>
	<a href="http://bit.ly/Uw2mEP" class="button" target="_blank" rel="nofollow"><b><?php _e( 'Donate', 'jpibfi' ); ?></b></a>
	<a href="<?php echo $current_settings['support_link']; ?>" class="button" target="_blank" rel="nofollow"><b><?php _e( 'Support forum', 'jpibfi' ); ?></b></a>
	<a href="<?php echo $current_settings['review_link']; ?>" class="button" target="_blank" rel="nofollow"><b><?php _e( 'Leave a review', 'jpibfi' ); ?></b></a>
</p>
<form method="post" action="options.php" ng-app="jpibfiApp" ng-controller="jpibfiController">
	<?php

	settings_fields( $current_settings[ 'settings_name'] );
	do_settings_sections( $current_settings[ 'settings_name'] );
	submit_button();
	?>
</form>
<p>
	The Silk Icon Set is provided by Mark James and is availble from <a href="http://famfamfam.com/lab/icons/silk/">FamFamFam</a>	under the	<a href="http://creativecommons.org/licenses/by/2.5/">Creative Commons Attribution 2.5 License</a>.
</p>
</div>