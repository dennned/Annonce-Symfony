'use strict';

$(document).ready(function () {
    var $urlElement = $('.js-category-url');
    $urlElement.prev().find('select').prop('disabled', true);

    $(document).on('change', '#form_category', function () {
        var category = $(this).find('option:selected').text();
        var url = $urlElement.data('category-url');

        if (category != 'Choice category') {
            $.ajax({
                url: url,
                method: "POST",
                data: {"category" : category}
            }).then(function (data) {
                $urlElement.prev().replaceWith(data);
            })
        } else {
            $urlElement.prev().find('select').prop('disabled', true);
        }

    });
});