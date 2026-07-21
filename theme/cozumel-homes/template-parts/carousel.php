<?php
// Renders the property photo/video carousel for the current post in the loop.
// Falls back to the featured image (or nothing) if gallery_ids is empty.
$gallery_ids = get_post_meta(get_the_ID(), 'gallery_ids', true);
if (!is_array($gallery_ids)) { $gallery_ids = []; }

// Drop any IDs pointing at attachments that no longer exist.
$gallery_ids = array_values(array_filter($gallery_ids, function ($id) {
    return get_post_status($id) !== false;
}));

if (empty($gallery_ids)) {
    if (has_post_thumbnail()) {
        echo '<div class="property-single__hero">';
        the_post_thumbnail('full', ['style' => 'width:100%;max-height:500px;object-fit:cover']);
        echo '</div>';
    }
    return;
}
?>
<div class="property-carousel">
    <div class="property-carousel__track">
        <?php foreach ($gallery_ids as $id): ?>
            <div class="property-carousel__slide">
                <?php if (wp_attachment_is('video', $id)): ?>
                    <video controls class="property-carousel__media">
                        <source src="<?php echo esc_url(wp_get_attachment_url($id)); ?>">
                    </video>
                <?php else: ?>
                    <img
                        src="<?php echo esc_url(wp_get_attachment_image_url($id, 'full')); ?>"
                        alt="<?php echo esc_attr(get_the_title()); ?>"
                        class="property-carousel__media"
                    >
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($gallery_ids) > 1): ?>
        <button type="button" class="property-carousel__arrow property-carousel__arrow--prev" aria-label="Previous photo">‹</button>
        <button type="button" class="property-carousel__arrow property-carousel__arrow--next" aria-label="Next photo">›</button>
        <div class="property-carousel__dots">
            <?php foreach ($gallery_ids as $i => $id): ?>
                <button
                    type="button"
                    class="property-carousel__dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
                    aria-label="Go to photo <?php echo esc_attr($i + 1); ?>"
                ></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
