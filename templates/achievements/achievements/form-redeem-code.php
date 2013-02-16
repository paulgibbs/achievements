<?php
/**
 * Achievement redemption form content part
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<form role="search" method="post" id="dpa-redemption-form">
	<?php do_action( 'dpa_template_notices' ); ?>

	<div>
		<label for="dpa-redemption-code"><?php _e( 'Enter code:', 'dpa' ); ?></label>
		<input id="dpa-redemption-code" name="dpa_code" type="text" required />
		<input class="button" id="dpa-redemption-submit" value="<?php esc_attr_e( 'Unlock', 'dpa' ); ?>" type="submit" />
	</div>

	<?php dpa_redeem_achievement_form_fields(); ?>
</form>
