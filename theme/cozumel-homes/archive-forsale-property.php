<?php get_header(); ?>
<main class="archive-page">
    <div class="section">
        <h1 class="section__title">Properties for Sale</h1>
        <p class="section__subtitle">Exceptional Cozumel real estate — represented by Kelley Morgan Gonzalez</p>
        <?php if (have_posts()): ?>
            <div class="properties-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/forsale-card'); ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No properties currently listed for sale. <a href="/contact/">Contact us</a> for more information.</p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
