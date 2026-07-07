<?php
/* Template Name: Contact */
get_header(); ?>
<main class="section" style="max-width:960px;margin:0 auto;padding:48px 24px">
    <h1 style="font-family:Georgia,serif">Contact Us</h1>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-top:32px">
        <div>
            <h3>Kelley Morgan Gonzalez</h3>
            <p>Property Manager &amp; Real Estate Agent<br>Cozumel, Quintana Roo, Mexico</p>
            <p>
                <strong>Email:</strong><br>
                <a href="mailto:home@cozumelhomes.net">home@cozumelhomes.net</a>
            </p>
            <p>
                <strong>Address:</strong><br>
                Avenida Rafael Melgar #602, Suite PA-6<br>
                Cozumel, Quintana Roo, Mexico 77600
            </p>
            <p>
                <a href="https://www.facebook.com/CozumelRentalHomes/" target="_blank" rel="noopener">Facebook</a>
                &nbsp;·&nbsp;
                <a href="https://mx.linkedin.com/in/kelley-morgan-gonzalez-89344666" target="_blank" rel="noopener">LinkedIn</a>
            </p>
        </div>
        <div>
            <?php cozumel_render_inquiry_form(); ?>
        </div>
    </div>
</main>
<?php get_footer(); ?>
