<?php get_header(); ?>
<main class="property-single">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $neighborhood     = get_post_meta(get_the_ID(), 'neighborhood', true);
        $address          = get_post_meta(get_the_ID(), 'address', true);
        $rate             = get_post_meta(get_the_ID(), 'base_rate', true);
        $base_guests      = get_post_meta(get_the_ID(), 'base_guests', true);
        $extra_guest_fee  = get_post_meta(get_the_ID(), 'extra_guest_fee', true);
        $guests           = get_post_meta(get_the_ID(), 'max_guests', true);
        $bedrooms         = get_post_meta(get_the_ID(), 'bedrooms', true);
        $bathrooms        = get_post_meta(get_the_ID(), 'bathrooms', true);
        ?>

        <?php get_template_part('template-parts/carousel'); ?>

        <div style="max-width:960px;margin:0 auto;padding:32px 24px">

            <h1 style="font-family:Georgia,serif;font-size:2.2rem;margin:0 0 8px"><?php the_title(); ?></h1>

            <?php if ($neighborhood): ?>
                <p class="property-single__neighborhood"><?php echo esc_html($neighborhood); ?></p>
            <?php endif; ?>

            <?php if ($guests || $bedrooms || $bathrooms): ?>
                <p class="property-single__specs">
                    <?php
                    $specs = array_filter([
                        $guests    ? "{$guests} guests"   : '',
                        $bedrooms  ? "{$bedrooms} bedrooms" : '',
                        $bathrooms ? "{$bathrooms} bathrooms" : '',
                    ]);
                    echo esc_html(implode(' · ', $specs));
                    ?>
                </p>
            <?php endif; ?>

            <?php if ($rate): ?>
                <p class="property-single__rate">
                    From $<?php echo esc_html(number_format((float)$rate)); ?> / night
                    <?php if ($base_guests): ?>
                        (up to <?php echo esc_html($base_guests); ?> guests)
                    <?php endif; ?>
                </p>
                <?php if ($extra_guest_fee): ?>
                    <p class="property-single__extra-guest-fee">
                        +$<?php echo esc_html(number_format((float)$extra_guest_fee)); ?> / night per extra guest
                    </p>
                <?php endif; ?>
            <?php endif; ?>

            <div class="property-single__description">
                <?php the_content(); ?>
            </div>

            <?php if ($address): ?>
                <p style="color:#6b6b6b;font-size:0.9rem"><?php echo esc_html($address); ?></p>
            <?php endif; ?>

            <?php get_template_part('template-parts/map'); ?>

            <?php get_template_part('template-parts/host-card'); ?>

            <div class="property-single__inquiry">
                <h3>Have a question or want to book?</h3>
                <?php cozumel_render_inquiry_form(get_the_title(), [
                    'property_id' => get_the_ID(),
                    'api_url'     => rest_url('cozumel/v1/availability/' . get_the_ID()),
                ]); ?>
            </div>

        </div>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
