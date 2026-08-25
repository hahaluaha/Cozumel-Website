jQuery(function ($) {
    var $list = $('#cozumel-gallery-list');
    if (!$list.length) return;

    var $input = $('#cozumel-gallery-ids-input');

    function syncInput() {
        var ids = $list.find('.cozumel-gallery-item').map(function () {
            return $(this).data('id');
        }).get();
        $input.val(ids.join(','));
    }

    $list.sortable({ update: syncInput });

    $('#cozumel-gallery-add').on('click', function (e) {
        e.preventDefault();
        var frame = wp.media({
            title: 'Select Gallery Photos',
            multiple: true,
            library: { type: ['image', 'video'] }
        });
        frame.on('select', function () {
            var selection = frame.state().get('selection');
            selection.each(function (attachment) {
                var data = attachment.toJSON();
                if ($list.find('.cozumel-gallery-item[data-id="' + data.id + '"]').length) {
                    return;
                }
                // Video attachments have no data.sizes.thumbnail — fall back
                // to wp.media's own generic icon (data.icon) rather than
                // data.url (which would try to render the raw .mp4 as an
                // <img>). Matches the PHP-rendered list's fallback.
                var isVideo = data.type === 'video';
                var thumbUrl = (data.sizes && data.sizes.thumbnail) ? data.sizes.thumbnail.url : (data.icon || data.url);
                var $item = $('<li class="cozumel-gallery-item" style="position:relative;cursor:move;text-align:center">')
                    .attr('data-id', data.id)
                    .append($('<img>').attr('src', thumbUrl).css({
                        width: 80, height: 80, objectFit: 'cover', borderRadius: 4, display: 'block',
                        border: isVideo ? '2px solid #2a6fa8' : 'none'
                    }));
                // Matches the PHP-rendered list's "VIDEO" badge (meta-fields.php)
                // — without this, a freshly-added video looks identical to a
                // photo until the page is reloaded and the PHP render takes over.
                if (isVideo) {
                    $item.append($('<span>VIDEO</span>').css({
                        position: 'absolute', top: 2, left: 2, background: 'rgba(0,0,0,.65)',
                        color: '#fff', fontSize: 9, padding: '1px 4px', borderRadius: 2, letterSpacing: '.03em'
                    }));
                }
                $item
                    .append($('<span>').text(data.filename || data.title || '').attr('title', data.filename || data.title || '').css({
                        display: 'block', fontSize: 10, maxWidth: 80, overflow: 'hidden',
                        textOverflow: 'ellipsis', whiteSpace: 'nowrap'
                    }))
                    .append($('<button type="button" class="cozumel-gallery-remove">×</button>').css({
                        position: 'absolute', top: -6, right: -6, background: '#c00', color: '#fff',
                        border: 'none', borderRadius: '50%', width: 20, height: 20, lineHeight: 1, cursor: 'pointer'
                    }));
                $list.append($item);
            });
            syncInput();
        });
        frame.open();
    });

    $list.on('click', '.cozumel-gallery-remove', function () {
        $(this).closest('.cozumel-gallery-item').remove();
        syncInput();
    });
});
