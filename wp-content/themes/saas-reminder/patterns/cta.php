<?php
/**
 * CTA Pattern
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */

return array(
    'title'      => __('Call to Action', 'saas-reminder'),
    'slug'       => 'saas-reminder/cta',
    'categories' => array('saas-reminder'),
    'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--60)","bottom":"var(--wp--preset--spacing--60)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--primary)"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="background-color:var(--wp--preset--color--primary);padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"var(--wp--preset--spacing--40)"}},"layout":{"type":"constrained","contentSize":"600px"}} -->
	<div class="wp-block-group">
		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--x-large)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--x-large)">Ready to get started?</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--large)"}}} -->
		<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">Join thousands of users who are already making their daily tasks effortless with our reminder app.</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			<!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Start free today</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->',
);
