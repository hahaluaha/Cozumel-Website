<?php
$price     = get_post_meta(get_the_ID(), 'asking_price', true);
$bedrooms  = get_post_meta(get_the_ID(), 'bedrooms', true);
$bathrooms = get_post_meta(get_the_ID(), 'bathrooms', true);
?>
<div class="property-card property-card--forsale">
    <a href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('medium_large', ['class' => 'property-card__image', 'alt' => get_the_title()]); ?>
        <?php else: ?>
            <div class="property-card__image property-card__image--placeholder"></div>
        <?php endif; ?>
        <div class="property-card__body">
            <h3 class="property-card__title"><?php the_title(); ?></h3>
            <?php if ($bedrooms || $bathrooms): ?>
                <p class="property-card__specs">
                    <?php
                    $specs = array_filter([
                        $bedrooms  ? "{$bedrooms} bed"   : '',
                        $bathrooms ? "{$bathrooms} bath"  : '',
                    ]);
                    echo esc_html(implode(' · ', $specs));
                    ?>
                </p>
            <?php endif; ?>
            <?php if ($price): ?>
                <p class="property-card__rate">$<?php echo esc_html(number_format((float)$price)); ?> USD</p>
            <?php endif; ?>
        </div>
    </a>
</div>
