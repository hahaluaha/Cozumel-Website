<?php get_header(); ?>
<main class="archive-page">
    <div class="section">
        <h1 class="section__title">Vacation Rentals</h1>
        <p class="section__subtitle">Premium properties in Cozumel, Mexico — managed by Kelley Morgan Gonzalez</p>
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
