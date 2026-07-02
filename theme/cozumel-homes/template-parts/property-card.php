<?php
$neighborhood = get_post_meta(get_the_ID(), 'neighborhood', true);
$guests       = get_post_meta(get_the_ID(), 'max_guests', true);
$bedrooms     = get_post_meta(get_the_ID(), 'bedrooms', true);
$bathrooms    = get_post_meta(get_the_ID(), 'bathrooms', true);
$rate         = get_post_meta(get_the_ID(), 'base_rate', true);
?>
<div class="property-card">
    <a href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()): ?>
            <?php the_post_thumbnail('medium_large', ['class' => 'property-card__image', 'alt' => get_the_title()]); ?>
        <?php else: ?>
            <div class="property-card__image property-card__image--placeholder"></div>
        <?php endif; ?>
        <div class="property-card__body">
            <h3 class="property-card__title"><?php the_title(); ?></h3>
            <?php if ($neighborhood): ?>
                <p class="property-card__neighborhood"><?php echo esc_html($neighborhood); ?></p>
            <?php endif; ?>
            <?php if ($guests || $bedrooms || $bathrooms): ?>
                <p class="property-card__specs">
                    <?php
                    $specs = array_filter([
                        $guests    ? "{$guests} guests" : '',
                        $bedrooms  ? "{$bedrooms} bed"  : '',
                        $bathrooms ? "{$bathrooms} bath" : '',
                    ]);
                    echo esc_html(implode(' · ', $specs));
                    ?>
                </p>
            <?php endif; ?>
            <?php if ($rate): ?>
                <p class="property-card__rate">$<?php echo esc_html(number_format((float)$rate)); ?> / night</p>
            <?php endif; ?>
        </div>
    </a>
</div>
