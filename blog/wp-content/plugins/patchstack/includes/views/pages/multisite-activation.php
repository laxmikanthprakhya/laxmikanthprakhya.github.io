<?php

// Do not allow the file to be called directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine which sites need activation or not.
$i             = 0;
$checkbox_list = '';
$activated     = '';
$sites         = get_sites();
foreach ( $sites as $site ) {
	if ( get_blog_option( $site->id, 'patchstack_clientid' ) == '' ) {
		$checkbox_list .= '<input type="checkbox" name="sites[]" id="site-' . esc_attr( $site->blog_id ) . '" value="' . esc_url( $site->siteurl ) . '"><label for="site-' . esc_attr( $site->blog_id ) . '">' . esc_url( $site->siteurl ) . '</label><br />';
		$i++;
	} else {
		$activated .= esc_url( $site->siteurl ) . '<br />';
	}
}
?>
<div class="patchstack-font">
	<h2 style="padding: 0;">Multisite Activation</h2>
	<p><?php echo wp_kses( $this->plugin->multisite->error, $this->allowed_html ); ?>
	Select the sites on which you would like to activate the Patchstack plugin. These sites must be accessible from the public internet.<br /><br>
	Note that if these sites have not been added to your Patchstack account yet, they will be added for you. Keep in mind that this might affect your upcoming bill depending on your current subscription plan.<br />
	You can also manually add your sites at <a href="https://app.patchstack.com/sites?add=1" target="_blank">app.patchstack.com</a> after which you can activate them on this page.<br><br />
	If you are an AppSumo user or have a limited amount of sites you can add, you must select the proper number of sites that can still be added to your account.</p>

	<h2 style="padding: 20px 0 0 0; display: <?php echo $i > 0 ? 'block' : 'none'; ?>;">Not Activated</h2>
	<form action="" method="POST" style="display: <?php echo $i > 0 ? 'block' : 'none'; ?>;">
		<input type="hidden" name="patchstack_do" value="do_licenses">
		<input type="hidden" value="<?php echo wp_create_nonce( 'patchstack-multisite-activation' ); ?>" name="PatchstackNonce">
		<?php echo wp_kses( $checkbox_list, $this->allowed_html ); ?>
		<br/>
		<input type="submit" class="button-primary" value="Activate" />
	</form>

	<br />
	<h2 style="padding: 0;">Activated</h2>
	<?php echo wp_kses( $activated, $this->allowed_html ); ?>
</div>
