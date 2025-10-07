<?php
/**
 * Hero Pattern
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */

return array(
    'title'      => __('Hero Section', 'saas-reminder'),
    'slug'       => 'saas-reminder/hero',
    'categories' => array('saas-reminder'),
    'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--70)","bottom":"var(--wp--preset--spacing--70)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--dark)"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="background-color:var(--wp--preset--color--dark);padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"var(--wp--preset--spacing--40)"}},"layout":{"type":"constrained","contentSize":"800px"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--xx-large)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--xx-large)">Make daily tasks effortless</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--large)"}}} -->
		<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">Never miss an important task again. Our smart reminder app helps you stay organized and productive with intelligent notifications and seamless integrations.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			<!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Start free</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
