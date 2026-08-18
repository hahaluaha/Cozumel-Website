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

// The LCP candidate is the first *image* slide, wherever it falls in the
// gallery order — a video-first gallery would otherwise never get an
// eager-loaded, high-priority image.
$first_image_index = null;
foreach ($gallery_ids as $i => $id) {
    if (!wp_attachment_is('video', $id)) {
        $first_image_index = $i;
        break;
    }
}
?>
<div class="property-carousel">
    <div class="property-carousel__track">
        <?php foreach ($gallery_ids as $i => $id): ?>
            <div class="property-carousel__slide">
                <?php if (wp_attachment_is('video', $id)): ?>
                    <?php $video_meta = wp_get_attachment_metadata($id); ?>
                    <video
                        controls
                        class="property-carousel__media"
                        preload="<?php echo $i === 0 ? 'auto' : 'none'; ?>"
                        <?php if (!empty($video_meta['width'])): ?>width="<?php echo esc_attr($video_meta['width']); ?>"<?php endif; ?>
                        <?php if (!empty($video_meta['height'])): ?>height="<?php echo esc_attr($video_meta['height']); ?>"<?php endif; ?>
                    >
                        <source src="<?php echo esc_url(wp_get_attachment_url($id)); ?>">
                    </video>
                <?php else: ?>
                    <?php
                    $is_lcp = ($i === $first_image_index);
                    echo wp_get_attachment_image($id, 'full', false, [
                        'alt'   => get_the_title(),
                        'class' => 'property-carousel__media',
                        'loading' => $is_lcp ? 'eager' : 'lazy',
                        'fetchpriority' => $is_lcp ? 'high' : 'auto',
                    ]);
                    ?>
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
