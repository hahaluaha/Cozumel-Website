<?php
$compact = !empty($args['compact']);
$photo_size = $compact ? 'medium' : 'large';
?>
<section class="host-card<?php echo $compact ? ' host-card--compact' : ''; ?>">
    <div class="host-card__photo-wrap">
        <img class="host-card__photo" src="<?php echo esc_url(wp_get_attachment_image_url(144, $photo_size)); ?>" alt="Kelley, host of Nah Ha 101, Cool Caribbean Views, and Casa Bohemia">
        <div class="host-card__wave" aria-hidden="true"></div>
    </div>
    <div class="host-card__body">
        <p class="host-card__eyebrow">Meet Your Host</p>
        <h2 class="host-card__title">Nearly 30 years hosting on Cozumel</h2>
        <p class="host-card__quote">"Not a big operation, just a host who knows the island and shows up for her guests."</p>
        <a href="<?php echo esc_url(home_url('/meet-your-hosts-kelley/')); ?>" class="host-card__link">Read Kelley's story →</a>
    </div>
</section>
