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
                var thumbUrl = (data.sizes && data.sizes.thumbnail) ? data.sizes.thumbnail.url : data.url;
                var $item = $('<li class="cozumel-gallery-item" style="position:relative;cursor:move">')
                    .attr('data-id', data.id)
                    .append($('<img>').attr('src', thumbUrl).css({
                        width: 80, height: 80, objectFit: 'cover', borderRadius: 4, display: 'block'
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
