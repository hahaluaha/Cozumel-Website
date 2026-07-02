<?php
$lat = get_post_meta(get_the_ID(), 'latitude', true);
$lng = get_post_meta(get_the_ID(), 'longitude', true);

if (!$lat || !$lng) {
    return; // No map if coordinates not set
}

$lat = floatval($lat);
$lng = floatval($lng);
?>
<div class="property-single__map">
<?php switch (COZUMEL_MAP_PROVIDER):
    case 'openstreetmap': ?>
        <iframe
            width="100%" height="350" frameborder="0" scrolling="no"
            src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo esc_attr($lng - 0.005); ?>,<?php echo esc_attr($lat - 0.005); ?>,<?php echo esc_attr($lng + 0.005); ?>,<?php echo esc_attr($lat + 0.005); ?>&layer=mapnik&marker=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lng); ?>"
        ></iframe>
        <?php break;
    case 'apple': ?>
        <div id="cozumel-map" style="width:100%;height:350px;background:#e8e8e8;display:flex;align-items:center;justify-content:center">
            <p style="color:#666">Map loading…</p>
        </div>
        <!-- When implementing: pin to a specific version and compute SRI hash (integrity="sha384-...") before deploying -->
        <script src="https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js" crossorigin="anonymous"></script>
        <script>
            mapkit.init({ authorizationCallback: function(done) { done('<?php echo esc_js(COZUMEL_MAPKIT_TOKEN); ?>'); } });
            var map = new mapkit.Map('cozumel-map');
            var coord = new mapkit.Coordinate(<?php echo $lat; ?>, <?php echo $lng; ?>);
            map.center = coord;
            map.addAnnotation(new mapkit.MarkerAnnotation(coord));
        </script>
        <?php break;
    case 'google':
    default: ?>
        <iframe
            width="100%" height="350" frameborder="0" style="border:0"
            src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr(COZUMEL_GOOGLE_MAPS_KEY); ?>&q=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lng); ?>&zoom=15"
            allowfullscreen
        ></iframe>
        <?php break;
endswitch; ?>
</div>
