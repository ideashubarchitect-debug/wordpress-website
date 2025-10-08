<?php
/**
 * The footer template file
 *
 * @package Aurora
 * @since 1.0.0
 */
?>

<?php
// Load the footer template part
if (file_exists(get_template_directory() . '/parts/footer.html')) {
    echo file_get_contents(get_template_directory() . '/parts/footer.html');
}
?>

<?php wp_footer(); ?>
</body>
</html>
