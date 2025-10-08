<?php
// Simple PHP file to serve our landing page
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaaS Reminder - Make daily tasks effortless</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #E8ECFF;
            background-color: #0B1020;
        }
        
        .hero {
            background-color: #0B1020;
            padding: 4rem 2rem;
            text-align: center;
            color: #E8ECFF;
        }
        
        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.5rem;
            color: #6B7280;
            margin-bottom: 2rem;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .btn-primary {
            background-color: #5B7CFF;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1.125rem;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background-color: #4A6BFF;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: #E8ECFF;
            padding: 12px 24px;
            text-decoration: none;
            border: 2px solid #E8ECFF;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1.125rem;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background-color: #E8ECFF;
            color: #0B1020;
        }
        
        .trust-row {
            background-color: #F8F9FF;
            padding: 3rem 2rem;
            text-align: center;
            color: #0B1020;
        }
        
        .features {
            background-color: white;
            padding: 5rem 2rem;
            color: #0B1020;
        }
        
        .features h2 {
            text-align: center;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 3rem;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background-color: #F8F9FF;
            padding: 3rem 2rem;
            border-radius: 12px;
            transition: transform 0.2s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
        }
        
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .feature-card p {
            color: #6B7280;
        }
        
        .cta {
            background-color: #5B7CFF;
            padding: 5rem 2rem;
            text-align: center;
            color: white;
        }
        
        .cta h2 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .cta p {
            font-size: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .cta .btn-primary {
            background-color: white;
            color: #5B7CFF;
        }
        
        .cta .btn-primary:hover {
            background-color: #F8F9FF;
        }
        
        .footer {
            background-color: #0F1535;
            padding: 2rem;
            text-align: center;
            color: #E8ECFF;
        }
        
        @media (max-width: 768px) {
            .buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero {
                padding: 2rem 1rem;
            }
            
            .features {
                padding: 3rem 1rem;
            }
            
            .cta {
                padding: 3rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div style="max-width: 900px; margin: 0 auto;">
            <h1>Make daily tasks effortless</h1>
            <p>Smart reminders via web, email, and WhatsApp. Never miss what matters.</p>
            <div class="buttons">
                <a href="#" class="btn-primary">Start free</a>
                <a href="#" class="btn-secondary">See pricing</a>
            </div>
            <p style="font-size: 0.875rem; color: #6B7280;">No card required • 14-day free trial</p>
        </div>
    </section>

    <!-- Trust Row -->
    <section class="trust-row">
        <div style="max-width: 1200px; margin: 0 auto;">
            <p style="color: #6B7280; font-size: 0.875rem; margin-bottom: 1rem;">Trusted by 50,000+ users worldwide</p>
            <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
                <p style="font-size: 0.875rem; font-weight: 600;">⭐⭐⭐⭐⭐ 4.9/5 rating</p>
                <p style="font-size: 0.875rem; font-weight: 600;">Google • Microsoft • Slack</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2>Everything you need to stay organized</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>⚡ Quick capture</h3>
                    <p>Add tasks in seconds with voice notes, quick text, or smart suggestions from your calendar.</p>
                </div>
                <div class="feature-card">
                    <h3>🧠 Smart schedules</h3>
                    <p>AI learns your patterns and suggests optimal reminder times based on your productivity habits.</p>
                </div>
                <div class="feature-card">
                    <h3>📱 Cross-device sync</h3>
                    <p>Access your tasks anywhere. Seamless sync across phone, desktop, and web with real-time updates.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section class="cta">
        <div style="max-width: 600px; margin: 0 auto;">
            <h2>Ready to get started?</h2>
            <p>Join thousands of users who save 2+ hours daily with smart reminders.</p>
            <a href="#" class="btn-primary">Start free</a>
            <p style="font-size: 0.875rem; margin-top: 1rem;">No card required • 14-day free trial • Cancel anytime</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 SaaS Reminder. All rights reserved.</p>
    </footer>
</body>
</html>
