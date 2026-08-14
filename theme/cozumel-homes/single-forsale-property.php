<?php get_header(); ?>
<main class="property-single">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $price       = get_post_meta(get_the_ID(), 'asking_price', true);
        $listing_url = get_post_meta(get_the_ID(), 'listing_url', true);
        $bedrooms    = get_post_meta(get_the_ID(), 'bedrooms', true);
        $bathrooms   = get_post_meta(get_the_ID(), 'bathrooms', true);
        $walkthrough_id = get_post_meta(get_the_ID(), 'walkthrough_video_id', true);
        ?>

        <?php get_template_part('template-parts/carousel'); ?>

        <div style="max-width:960px;margin:0 auto;padding:32px 24px">

            <h1 style="font-family:Georgia,serif;font-size:2.2rem;margin:0 0 8px"><?php the_title(); ?></h1>

            <?php if ($walkthrough_id):
                $video_url = wp_get_attachment_url($walkthrough_id);
                $poster_url = wp_get_attachment_image_url(get_post_thumbnail_id(), 'large');
                if (!$poster_url && $video_url) {
                    $candidate_path = str_replace(content_url(), WP_CONTENT_DIR, preg_replace('/\.mp4$/i', '-poster.jpg', $video_url));
                    if (file_exists($candidate_path)) {
                        $poster_url = preg_replace('/\.mp4$/i', '-poster.jpg', $video_url);
                    }
                }
            ?>
                <div class="property-video--vertical">
                    <video controls preload="metadata" playsinline
                           poster="<?php echo esc_url($poster_url ?: ''); ?>">
                        <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                    </video>
                </div>
            <?php endif; ?>

            <?php if ($bedrooms || $bathrooms): ?>
                <p class="property-single__specs">
                    <?php
                    $specs = array_filter([
                        $bedrooms  ? "{$bedrooms} bedrooms"  : '',
                        $bathrooms ? "{$bathrooms} bathrooms" : '',
                    ]);
                    echo esc_html(implode(' · ', $specs));
                    ?>
                </p>
            <?php endif; ?>

            <?php if ($price): ?>
                <p class="property-single__rate">$<?php echo esc_html(number_format((float)$price)); ?> USD</p>
            <?php endif; ?>

            <?php if ($listing_url): ?>
                <div style="margin:24px 0">
                    <a href="<?php echo esc_url($listing_url); ?>" class="btn btn--primary" target="_blank" rel="noopener noreferrer">View Full Listing</a>
                </div>
            <?php endif; ?>

            <div class="property-single__description">
                <?php the_content(); ?>
            </div>

            <?php get_template_part('template-parts/map'); ?>

            <div class="property-single__inquiry">
                <h3>Interested in this property?</h3>
                <?php cozumel_render_inquiry_form(get_the_title()); ?>
            </div>

        </div>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
