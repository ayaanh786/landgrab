<script>
// 
// Tutorial Logic
// 

if (log_check && account['tutorial'] < 5) {
    $('.exit_tutorial').click(function(){
        $('#tutorial_block').hide();
    });

    if (account['tutorial'] == 0) {
        setTimeout(function(){
            $('.user_button').click();
            $('#tutorial_block').fadeIn();
            $('#tutorial_title').html('Birth of a nation');
            $('#tutorial_text').html('Choose the name and symbols of your nation.');
        }, 4 * 1000);
    } else if (account['tutorial'] == 1) {
        $('#tutorial_block').fadeIn();
        $('#tutorial_title').html('Declare Independence');
        $('#tutorial_text').html('Click the map to pick a starting location for your Capitol');
    // Also see map script for tutorial duplicate of this
    } else if (account['tutorial'] == 2) {
        $('#tutorial_block').fadeIn();
        $('#tutorial_title').html('We The People');
        $('#tutorial_text').html('Pick a form of Government, set a tax rate, and balance your budget. Read the Gameplay Guide to learn advanced gameplay mechanics.');
        $('#stat_dropdown').click();
    } else if (account['tutorial'] == 3) {
        $('#tutorial_block').fadeIn();
        $('#tutorial_title').html('Building for the Future');
        $('#tutorial_text').html('Click on your Capitol, and build something to improve your nation.');
    } else if (account['tutorial'] == 4) {
        $('#tutorial_block').fadeIn();
        $('#tutorial_title').html('Manifest Destiny');
        $('#tutorial_text').html('Conquer the world. War Weariness will accumulate as you acquire territory and will go back down over time.');
        account['tutorial'] = 4;
    }
}

</script>