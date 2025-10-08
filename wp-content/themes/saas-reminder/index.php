<?php
/**
 * The main template file
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */

get_header();
?>

<main id="main" class="site-main">
	<!-- Hero Section -->
	<div class="hero-section" style="background-color: #0B1020; padding: 4rem 2rem; text-align: center; color: #E8ECFF;">
		<div style="max-width: 900px; margin: 0 auto;">
			<h1 style="font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 800; line-height: 1.1; margin: 0 0 1rem 0;">Make daily tasks effortless</h1>
			<p style="font-size: 1.5rem; color: #6B7280; margin: 0 0 2rem 0;">Smart reminders via web, email, and WhatsApp. Never miss what matters.</p>
			<div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1rem;">
				<a href="#" style="background-color: #5B7CFF; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 1.125rem;">Start free</a>
				<a href="#" style="background-color: transparent; color: #E8ECFF; padding: 12px 24px; text-decoration: none; border: 2px solid #E8ECFF; border-radius: 6px; font-weight: 600; font-size: 1.125rem;">See pricing</a>
			</div>
			<p style="font-size: 0.875rem; color: #6B7280; margin: 0;">No card required • 14-day free trial</p>
		</div>
	</div>

	<!-- Trust Row -->
	<div style="background-color: #F8F9FF; padding: 3rem 2rem; text-align: center;">
		<div style="max-width: 1200px; margin: 0 auto;">
			<p style="color: #6B7280; font-size: 0.875rem; margin: 0 0 1rem 0;">Trusted by 50,000+ users worldwide</p>
			<div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
				<p style="font-size: 0.875rem; font-weight: 600; margin: 0;">⭐⭐⭐⭐⭐ 4.9/5 rating</p>
				<p style="font-size: 0.875rem; font-weight: 600; margin: 0;">Google • Microsoft • Slack</p>
			</div>
		</div>
	</div>

	<!-- Features Section -->
	<div style="background-color: white; padding: 5rem 2rem;">
		<div style="max-width: 1200px; margin: 0 auto;">
			<h2 style="text-align: center; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; color: #0B1020; margin: 0 0 3rem 0;">Everything you need to stay organized</h2>
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
				<div style="background-color: #F8F9FF; padding: 3rem 2rem; border-radius: 12px;">
					<h3 style="font-size: 1.25rem; font-weight: 600; color: #0B1020; margin: 0 0 1rem 0;">⚡ Quick capture</h3>
					<p style="font-size: 1rem; color: #6B7280; margin: 0;">Add tasks in seconds with voice notes, quick text, or smart suggestions from your calendar.</p>
				</div>
				<div style="background-color: #F8F9FF; padding: 3rem 2rem; border-radius: 12px;">
					<h3 style="font-size: 1.25rem; font-weight: 600; color: #0B1020; margin: 0 0 1rem 0;">🧠 Smart schedules</h3>
					<p style="font-size: 1rem; color: #6B7280; margin: 0;">AI learns your patterns and suggests optimal reminder times based on your productivity habits.</p>
				</div>
				<div style="background-color: #F8F9FF; padding: 3rem 2rem; border-radius: 12px;">
					<h3 style="font-size: 1.25rem; font-weight: 600; color: #0B1020; margin: 0 0 1rem 0;">📱 Cross-device sync</h3>
					<p style="font-size: 1rem; color: #6B7280; margin: 0;">Access your tasks anywhere. Seamless sync across phone, desktop, and web with real-time updates.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Final CTA Section -->
	<div style="background-color: #5B7CFF; padding: 5rem 2rem; text-align: center; color: white;">
		<div style="max-width: 600px; margin: 0 auto;">
			<h2 style="font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; margin: 0 0 1rem 0;">Ready to get started?</h2>
			<p style="font-size: 1.25rem; margin: 0 0 2rem 0;">Join thousands of users who save 2+ hours daily with smart reminders.</p>
			<a href="#" style="background-color: white; color: #5B7CFF; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 1.125rem; display: inline-block; margin-bottom: 1rem;">Start free</a>
			<p style="font-size: 0.875rem; margin: 0;">No card required • 14-day free trial • Cancel anytime</p>
		</div>
	</div>
</main>

<?php
get_footer();
