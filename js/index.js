$(document).ready(function() {
    $('button').on('click', function(e) {
        e.preventDefault();

        var tel = $('#tel').val();

        $('#output').empty();

        $('.form-control').on('focus', function() {
            $('.form-group-lg').removeClass('has-error');
        });

        if ('' !== tel) {
            $.ajax({
                type: 'POST',
                url: 'validate.php',
                dataType: 'JSON',
                data: {
                    tel: tel
                },
                complete: function(response) {
                    var r = JSON.parse(response.responseText);

                    if ('' !== r.message) {
                        $('<p>').text(r.message).prependTo('#output');
                    }

                    if (null !== r.data) {
                        $('<table class="table">').appendTo('#output');
                        $('<thead>').appendTo('table');
                        $('<tbody>').appendTo('table');
                        $('<tr>').appendTo('thead');

                        $.each(r.data[0], function(k, v) {
                            $('<th>').text(v.name).appendTo('thead tr');
                        });

                        $.each(r.data, function(k, v) {
                            $('<tr>').appendTo('tbody');
                            $.each(v, function(k, v) {
                                $('<td>').text(v.value).appendTo('tbody tr:last');
                            });
                        });
                    }

                }
            });
        } else {
            $('.form-group-lg').addClass('has-error');
        }
    });

});

