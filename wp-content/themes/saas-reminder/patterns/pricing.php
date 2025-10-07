<?php
/**
 * Pricing Pattern
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */

return array(
    'title'      => __('Pricing Section', 'saas-reminder'),
    'slug'       => 'saas-reminder/pricing',
    'categories' => array('saas-reminder'),
    'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--60)","bottom":"var(--wp--preset--spacing--60)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--dark)"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="background-color:var(--wp--preset--color--dark);padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40)">
	<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--x-large)"},"spacing":{"margin":{"bottom":"var(--wp--preset--spacing--50)"}}}} -->
	<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--50);font-size:var(--wp--preset--font-size--x-large)">Simple, transparent pricing</h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var(--wp--preset--spacing--40)"}}}} -->
	<div class="wp-block-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--50)","bottom":"var(--wp--preset--spacing--50)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--surface)"}},"border":{"radius":"8px"}},"className":"is-style-card"} -->
			<div class="wp-block-group is-style-card" style="border-radius:8px;background-color:var(--wp--preset--color--surface);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:heading {"level":3,"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--large)"}}} -->
				<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">Starter</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--x-large)","fontWeight":"700"}}} -->
				<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--x-large);font-weight:700">Free</p>
				<!-- /wp:paragraph -->

				<!-- wp:list {"style":{"spacing":{"padding":{"left":"0"}},"typography":{"fontSize":"var(--wp--preset--font-size--small)"}},"className":"is-style-default"} -->
				<ul class="is-style-default" style="font-size:var(--wp--preset--font-size--small);padding-left:0">
					<!-- wp:list-item -->
					<li>Up to 10 reminders</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Basic notifications</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Email support</li>
					<!-- /wp:list-item -->
				</ul>
				<!-- /wp:list -->

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
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--50)","bottom":"var(--wp--preset--spacing--50)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--primary)"}},"border":{"radius":"8px"}},"className":"is-style-card"} -->
			<div class="wp-block-group is-style-card" style="border-radius:8px;background-color:var(--wp--preset--color--primary);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:heading {"level":3,"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--large)"}}} -->
				<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">Pro</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--x-large)","fontWeight":"700"}}} -->
				<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--x-large);font-weight:700">$9<span style="font-size:var(--wp--preset--font-size--medium)">/month</span></p>
				<!-- /wp:paragraph -->

				<!-- wp:list {"style":{"spacing":{"padding":{"left":"0"}},"typography":{"fontSize":"var(--wp--preset--font-size--small)"}},"className":"is-style-default"} -->
				<ul class="is-style-default" style="font-size:var(--wp--preset--font-size--small);padding-left:0">
					<!-- wp:list-item -->
					<li>Unlimited reminders</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Smart notifications</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>API access</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Priority support</li>
					<!-- /wp:list-item -->
				</ul>
				<!-- /wp:list -->

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
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--50)","bottom":"var(--wp--preset--spacing--50)","left":"var(--wp--preset--spacing--40)","right":"var(--wp--preset--spacing--40)"}},"color":{"background":"var(--wp--preset--color--surface)"}},"border":{"radius":"8px"}},"className":"is-style-card"} -->
			<div class="wp-block-group is-style-card" style="border-radius:8px;background-color:var(--wp--preset--color--surface);padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:heading {"level":3,"textAlign":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--large)"}}} -->
				<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--large)">Business</h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"var(--wp--preset--font-size--x-large)","fontWeight":"700"}}} -->
				<p class="has-text-align-center" style="font-size:var(--wp--preset--font-size--x-large);font-weight:700">$29<span style="font-size:var(--wp--preset--font-size--medium)">/month</span></p>
				<!-- /wp:paragraph -->

				<!-- wp:list {"style":{"spacing":{"padding":{"left":"0"}},"typography":{"fontSize":"var(--wp--preset--font-size--small)"}},"className":"is-style-default"} -->
				<ul class="is-style-default" style="font-size:var(--wp--preset--font-size--small);padding-left:0">
					<!-- wp:list-item -->
					<li>Everything in Pro</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Team collaboration</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Advanced analytics</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Custom integrations</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>24/7 support</li>
					<!-- /wp:list-item -->
				</ul>
				<!-- /wp:list -->

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
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->',
);
