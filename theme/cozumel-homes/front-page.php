<?php get_header(); ?>
<main>

    <!-- Hero -->
    <section class="hero">
        <div class="hero__slide"><img src="<?php echo esc_url( wp_get_attachment_image_url( 130, 'full' ) ); ?>" alt="Nah Ha 101 sunset pool"></div>
        <div class="hero__slide"><img src="<?php echo esc_url( wp_get_attachment_image_url( 131, 'full' ) ); ?>" alt="Cool Caribbean Views ocean view"></div>
        <div class="hero__slide"><img src="<?php echo esc_url( wp_get_attachment_image_url( 132, 'full' ) ); ?>" alt="Casa Bohemia snorkel masks"></div>
        <div class="hero__scrim"></div>

        <div class="hero__panel">
            <p class="hero__eyebrow">Cozumel, Mexico</p>
            <h1 class="hero__title">Your island story, <em>waiting</em></h1>
            <p class="hero__tagline">Premium vacation rentals and real estate, hand-managed by someone who actually lives here.</p>
            <div class="hero__ctas">
                <a href="/rentals/" class="btn btn--primary">View Rentals →</a>
                <a href="/for-sale/" class="btn btn--outline">Properties for Sale</a>
            </div>
        </div>

        <div class="hero__dots" aria-hidden="true">
            <div class="hero__dot"></div>
            <div class="hero__dot"></div>
            <div class="hero__dot"></div>
        </div>

        <svg class="hero__wave" viewBox="0 0 1200 60" preserveAspectRatio="none">
            <defs>
                <linearGradient id="hero-wave-grad" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#1c8fa6"></stop>
                    <stop offset="50%" stop-color="#2eb3c4"></stop>
                    <stop offset="100%" stop-color="#1c8fa6"></stop>
                </linearGradient>
            </defs>
            <path d="M0,30 C150,60 350,0 600,25 C850,50 1050,5 1200,30 L1200,60 L0,60 Z" fill="url(#hero-wave-grad)"></path>
        </svg>
    </section>

    <!-- About Kelley -->
    <section class="section" style="border-bottom:1px solid #eee">
        <div style="max-width:760px">
            <h2 class="section__title">Boutique Property Management</h2>
            <p style="font-size:1.1rem;line-height:1.8;color:#444">
                Kelley Morgan Gonzalez brings over 20 years of island experience to every stay.
                Whether you're planning a diving escape, a family holiday, or searching for your
                dream property in Cozumel, you'll receive friendly, personalized service from
                someone who truly knows this island.
            </p>
            <p style="color:#6b6b6b">
                <a href="mailto:home@cozumelhomes.net">home@cozumelhomes.net</a>
            </p>
        </div>
    </section>

    <!-- Rental Properties -->
    <section class="section">
        <h2 class="section__title">Vacation Rentals</h2>
        <p class="section__subtitle">Hand-selected properties across Cozumel's most sought-after neighborhoods</p>
        <div class="properties-grid">
            <?php
            $rentals = new WP_Query([
                'post_type'      => 'rental-property',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
            ]);
            if ($rentals->have_posts()):
                while ($rentals->have_posts()) : $rentals->the_post();
                    get_template_part('template-parts/property-card');
                endwhile;
                wp_reset_postdata();
            else: ?>
                <p>Rental properties coming soon.</p>
            <?php endif; ?>
        </div>
        <p style="margin-top:24px"><a href="/rentals/" class="btn btn--outline">View All Rentals</a></p>
    </section>

    <!-- For Sale -->
    <?php
    $forsale = new WP_Query([
        'post_type'      => 'forsale-property',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
    ]);
    if ($forsale->have_posts()): ?>
        <section class="section" style="background:#f9f6f0">
            <h2 class="section__title">Properties for Sale</h2>
            <p class="section__subtitle">Exceptional Cozumel real estate represented by Kelley Morgan Gonzalez</p>
            <div class="properties-grid">
                <?php while ($forsale->have_posts()) : $forsale->the_post();
                    get_template_part('template-parts/forsale-card');
                endwhile;
                wp_reset_postdata(); ?>
            </div>
            <p style="margin-top:24px"><a href="/for-sale/" class="btn btn--outline">View All For Sale</a></p>
        </section>
    <?php endif; ?>

    <!-- Testimonials -->
    <section class="testimonials">
        <div style="max-width:1100px;margin:0 auto">
            <h2 class="section__title" style="text-align:center;margin-bottom:32px">What Our Guests Say</h2>
            <div class="testimonials__grid">
                <div class="testimonial">
                    <p class="testimonial__text">"The property exceeded our expectations in every way. Kelley was incredibly responsive and made our stay in Cozumel truly memorable."</p>
                    <p class="testimonial__author">— Guest, Nah Ha Condominium 101</p>
                </div>
                <div class="testimonial">
                    <p class="testimonial__text">"Vistas increíbles al mar — incredible ocean views. The apartment is exactly as described and Kelley's local knowledge made all the difference."</p>
                    <p class="testimonial__author">— Guest, Cool Caribbean Views</p>
                </div>
                <div class="testimonial">
                    <p class="testimonial__text">"We loved staying at Casa Bohemia. A bright, airy home in a wonderful neighborhood — perfect for our family vacation to Cozumel."</p>
                    <p class="testimonial__author">— Guest, Casa Bohemia</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Home Inquiry -->
    <section class="section" style="max-width:760px">
        <h2 class="section__title">Plan Your Cozumel Stay</h2>
        <p class="section__subtitle">Send us your dates and we'll find the right property for you.</p>
        <?php cozumel_render_inquiry_form(); ?>
    </section>

</main>
<?php get_footer(); ?>
