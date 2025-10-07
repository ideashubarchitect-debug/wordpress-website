<?php
/**
 * The header template file
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php _e('Skip to content', 'saas-reminder'); ?></a>

<?php
// Load the header template part
if (file_exists(get_template_directory() . '/parts/header.html')) {
    echo file_get_contents(get_template_directory() . '/parts/header.html');
}
?>
