<?php
/* Template Name: Contact */
get_header(); ?>
<main class="section" style="max-width:960px;margin:0 auto;padding:48px 24px">
    <h1 style="font-family:Georgia,serif">Contact Us</h1>
    <div class="contact-grid" style="margin-top:32px">
        <div>
            <h3>Kelley Morgan Gonzalez</h3>
            <p>Property Manager &amp; Real Estate Agent<br>Cozumel, Quintana Roo, Mexico</p>
            <p>
                <strong>Email:</strong><br>
                <a href="mailto:home@cozumelhomes.net">home@cozumelhomes.net</a>
            </p>
            <div class="contact-whatsapp">
                <p>
                    <strong>WhatsApp:</strong><br>
                    <a href="https://wa.me/529878760638" target="_blank" rel="noopener">+52 987 876 0638</a><br>
                    <span style="font-size:0.85rem;color:var(--color-muted)">Mon&ndash;Fri, 9am&ndash;5pm &middot; Cozumel time</span>
                </p>
                <?php echo wp_get_attachment_image(135, 'medium', false, ['style' => 'max-width:160px;height:auto;margin-top:8px;border-radius:8px', 'loading' => 'lazy']); ?>
            </div>
            <p>
                <strong>Address:</strong><br>
                Avenida Rafael Melgar #602, Suite PA-6<br>
                Cozumel, Quintana Roo, Mexico 77600
            </p>
            <p>
                <a href="https://www.facebook.com/CozumelRentalHomes/" target="_blank" rel="noopener">Facebook</a>
            </p>
        </div>
        <div>
            <?php cozumel_render_inquiry_form(); ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
