<?php get_header(); ?>
<main class="archive-page">
    <div class="section">
        <h1 class="section__title">Cozumel Home Rentals</h1>
        <p class="section__subtitle">Book direct with Kelley, hosting on Cozumel since 1997</p>
        <p class="section__intro">
            Three homes for rent in Cozumel, each in a different part of the island:
            <strong>Cool Caribbean Views</strong>, an oceanfront apartment on the downtown
            malecón; <strong>Nah Ha 101</strong>, a North Shore condo above the water; and
            <strong>Casa Bohemia</strong> — literally a house for rent, <em>una casa en
            renta</em>, in the residential Corpus Christi neighborhood. All three are
            pet-friendly ($50 non-refundable deposit per pet), all give 10% off stays of
            7+ nights, and all offer a monthly rate for longer trips.
        </p>
        <?php if (have_posts()): ?>
            <div class="properties-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/property-card'); ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No properties available at this time. <a href="/contact/">Contact us</a> to learn more.</p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
