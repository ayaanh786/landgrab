<script>

    handle_edit_tile_meta();
    handle_first_claim();

    function handle_edit_tile_meta() {
        $('#edit_tile_name').click(function(event) {
            $('#edit_tile_name, #tile_name').hide();
            $('#tile_name_input, #submit_tile_name').show();
        });
        $('#edit_tile_desc').click(function(event) {
            $('#edit_tile_desc, #tile_desc').hide();
            $('#tile_desc_input, #submit_tile_desc').show();
        });
        $('#submit_tile_name').click(function(event) {
            $.ajax({
                url: "<?=base_url()?>game/update_tile_name",
                type: "POST",
                data: {
                    tile_id: current_tile.id,
                    tile_name: $('#tile_name_input').val(),
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response['error']) {
                        alert(response['error_message']);
                        return false;
                    }
                    $('#tile_name').html($('#tile_name_input').val());
                    $('#edit_tile_name, #tile_name').show();
                    $('#tile_name_input, #submit_tile_name').hide();
                }
            });
        });
        $('#submit_tile_desc').click(function(event) {
            $.ajax({
                url: "<?=base_url()?>game/update_tile_desc",
                type: "POST",
                data: {
                    tile_id: current_tile.id,
                    tile_desc: $('#tile_desc_input').val(),
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response['error']) {
                        alert(response['error_message']);
                        return false;
                    }
                    $('#tile_desc').html(nl2br($('#tile_desc_input').val()));
                    $('#edit_tile_desc, #tile_desc').show();
                    $('#tile_desc_input, #submit_tile_desc').hide();
                }
            });
        });
    }

    function handle_first_claim() {
        $('#do_first_claim').click(function(){
            do_first_claim(function(){
                get_map_update();
            });
        });
    }

    function do_first_claim(callback) {
        $.ajax({
            url: "<?=base_url()?>game/do_first_claim",
            type: "POST",
            data: {
                world_key: current_tile.world_key,
                lat: current_tile.lat,
                lng: current_tile.lng,
            },
            cache: false,
            success: function(data) {
                callback(data);
            }
        });
    }

</script>