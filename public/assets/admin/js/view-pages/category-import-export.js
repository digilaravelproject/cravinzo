"use strict";

$(document).on('ready', function () {
    $('#date_from').attr('max', (new Date()).toISOString().split('T')[0]);
    $('#date_to').attr('max', (new Date()).toISOString().split('T')[0]);

    $('.id_wise').hide();
    $('.date_wise').hide();

    $('#type').on('change', function () {
        const selectedType = $(this).val();
        $('.id_wise, .date_wise').hide();
        if (selectedType === 'id_wise') {
            $('.id_wise').show();
        } else if (selectedType === 'date_wise') {
            $('.date_wise').show();
        }
    });

    $('.btn--reset').on('click', function (e) {
        e.preventDefault();
        const form = $(this).closest('form')[0];
        form.reset();

        $('.id_wise, .date_wise').hide();
        $('#type').val('all');
        $('#type').trigger('change');
    });
});
