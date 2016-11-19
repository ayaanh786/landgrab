<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class Game extends CI_Controller {

    protected $unclaimed_key = 1;
    protected $village_key = 2;
    protected $town_key = 3;
    protected $city_key = 4;
    protected $metropolis_key = 5;
    protected $capitol_key = 6;

    protected $anarchy_key = 0;
    protected $democracy_key = 1;
    protected $oligarchy_key = 2;
    protected $autocracy_key = 3;

	function __construct() {
	    parent::__construct();
        $this->load->model('game_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('action_model', '', TRUE);
	    $this->load->model('leaderboard_model', '', TRUE);
	}

	// Game view and update json
	public function index($world_slug = 1, $marketing_slug = false)
	{
        // Record marketing slug into analytics table
        if ($marketing_slug) {
            $this->user_model->record_marketing_hit($marketing_slug);
        }

        // Authentication
        $log_check = $data['log_check'] = $data['user_id'] = false;
        if ($this->session->userdata('logged_in')) {
            $log_check = $data['log_check'] = true;
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
            $data['user'] = $this->user_model->get_user($user_id);
            if (! isset($data['user']['username']) ) {
                redirect('user/logout', 'refresh');
                return false;
            }
        }

        // Get world
        $world = $data['world'] = $this->game_model->get_world_by_slug_or_id($world_slug);

        // Return 404 if world not found
        if (!$world) {
            $this->load->view('errors/page_not_found', $data);
            return false;
        }

        // If logged in, get full account information
        if ($log_check) {
            $account = $this->user_model->get_account_by_keys($user_id, $world['id']);
            $data['account'] = $this->get_full_account($account);
        }

        // Get world leaderboards
        $data['leaderboards'] = $this->leaderboards($world);

        // Get all worlds
        $data['worlds'] = $this->user_model->get_all_worlds();

        // Get dictionaries
        $data['government_dictionary'] = $this->government_dictionary();
        $data['stroke_color_dictionary'] = $this->stroke_color_dictionary();
        $modify_effect_dictionary = $data['modify_effect_dictionary'] = $this->game_model->get_all_modify_effects();

        // Get all lands
        $server_update_timespan = 20;
        $data['update_timespan'] = ($server_update_timespan / 2) * 1000;
        if (isset($_GET['json'])) {
            $data['lands'] = $this->game_model->get_all_lands_in_world_recently_updated($world['id'], $server_update_timespan);
        }
        else {
            $data['lands'] = $this->game_model->get_all_lands_in_world($world['id']);
        }

        // Validation errors
        $data['validation_errors'] = $this->session->flashdata('validation_errors');
        $data['failed_form'] = $this->session->flashdata('failed_form');
        $data['just_registered'] = $this->session->flashdata('just_registered');

        // If data request, encode data in json and deliver
        if (isset($_GET['json'])) {
            // Send refresh signal to clients when true
            $data['refresh'] = false;

            // Encode and send data
            function filter(&$value) {
              $value = nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            array_walk_recursive($data, "filter");
            echo json_encode($data);
            return true;
        }

        // Load view
        $this->load->view('header', $data);
        $this->load->view('menus', $data);
        $this->load->view('blocks', $data);
        $this->load->view('land_block', $data);
        $this->load->view('leaderboards', $data);
        $this->load->view('map_script', $data);
        $this->load->view('interface_script', $data);
        $this->load->view('tutorial_script', $data);
        $this->load->view('chat_script', $data);
        $this->load->view('footer', $data);
	}

    public function get_full_account($account)
    {
        // Get account
        $account['land_count'] = $account['land_count'] = $this->user_model->get_count_of_account_land($account['id']);
        $account['stats'] = $this->game_model->get_sum_effects_for_account($account['id']);

        // Democracy Taxes
        if ($account['government'] == $this->democracy_key) {
            $account['effective_tax_rate'] = $account['tax_rate'];
        }
        // Oligarchy Taxes
        else if ($account['government'] == $this->oligarchy_key) {
            $account['effective_tax_rate'] = floor($account['tax_rate'] * 0.25);
        }
        // Autocracy Taxes
        else if ($account['government'] == $this->autocracy_key) {
            $account['effective_tax_rate'] = floor($account['tax_rate'] * 0.5);
        }
        // Anarchy
        else {
            $account['effective_tax_rate'] = 0;
        }
        $account['stats']['corruption_rate'] = $account['tax_rate'] - $account['effective_tax_rate'];
        $account['stats']['tax_income'] = floor( $account['stats']['gdp'] * ($account['effective_tax_rate'] / 100) );
        $account['stats']['military_after'] = floor($account['stats']['military'] + ($account['stats']['tax_income'] * ($account['military_budget'] / 100) ) );
        $account['stats']['entitlements'] = floor($account['stats']['tax_income'] * ($account['entitlements_budget'] / 100) );
        $account['stats']['treasury_after'] = floor($account['stats']['tax_income'] - $account['stats']['military'] - $account['stats']['entitlements']);
        $account['stats']['support'] = 100 - $account['war_weariness'] - $account['tax_rate'] + $account['entitlements_budget'] + $account['stats']['support'];
        $account['stats']['war_weariness'] = $account['war_weariness'];
        // No support for anarchy
        if ($account['government'] == $this->anarchy_key) {
            $account['stats']['support'] = 0;
        }

        // See if functioning
        $account['functioning'] = true;
        if ($account['government'] == $this->democracy_key && $account['stats']['support'] < 50) {
            $account['functioning'] = false;
        }
        else if ($account['government'] == $this->oligarchy_key && $account['stats']['support'] < 30) {
            $account['functioning'] = false;
        }
        else if ($account['government'] == $this->autocracy_key && $account['stats']['support'] < 10) {
            $account['functioning'] = false;
        } else if ($account['government'] == $this->anarchy_key) {
            $account['functioning'] = false;
        }

        // Record account as loaded
        $query_action = $this->user_model->account_loaded($account['id']);

        // Return account
        return $account;
    }

	// Get infomation on single land
	public function get_single_land($coord_slug = false, $world_key = false)
	{
        // Get input
        if ($coord_slug && $world_key) {
            $json_output = false;
        }
        else if (isset($_GET['coord_slug']) && isset($_GET['world_key']) ) {
            $json_output = true;
            $coord_slug = $_GET['coord_slug'];
            $world_key = $_GET['world_key'];
        }
        else {
            echo '{"error": "Input missing"}';
            return false;
        }

	    // Get Land Square
        $land_square = $this->game_model->get_single_land($world_key, $coord_slug);
        $land_square['effects'] = $this->game_model->get_effects_of_land($land_square['id']);
        $land_square['sum_effects'] = $this->game_model->get_sum_effects_of_land($land_square['id']);
        $land_square['sum_modifiers'] = $this->game_model->get_sum_modifiers_for_land($land_square['id']);
        $account = $land_square['account'] = $this->user_model->get_account_by_id($land_square['account_key']);

        // War Weariness
        $land_square['war_weariness'] = 0;
        if ($this->session->userdata('logged_in')) {
            $session_data = $this->session->userdata('logged_in');
            $user_id = $session_data['id'];
            $requester_account = $this->user_model->get_account_by_keys($user_id, $world_key);
            $requester_account = $this->get_full_account($requester_account);
            $land_square['war_weariness'] = $this->war_weariness_calculate($requester_account, $land_square['account_key']);
        }


        // Land range false by default
        $land_square['in_range'] = false;

        // Add username to array
        $owner = $this->user_model->get_user($account['user_key']);
        if ( isset($owner['username']) && isset($land_square['land_name']) ) {
            $land_square['username'] = $owner['username'];
        }
        else {
            $land_square['username'] = '';
        }

        // Get account
        $log_check = false;
        if ($this->session->userdata('logged_in')) {
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
            $account = $this->user_model->get_account_by_keys($user_id, $world_key);
            $world = $data['world'] = $this->game_model->get_world_by_slug_or_id($world_key);
            $account['land_count'] = $data['account']['land_count'] = $this->user_model->get_count_of_account_land($account['id']);
            $log_check = true;
            // Check if land is in range
            $land_square['in_range'] = $this->check_if_land_is_in_range($world_key, $account['id'], $account['land_count'], 
                $world['land_size'], $land_square['lat'], $land_square['lng'], false);
            $land_square['range_check'] = $this->check_if_land_is_in_range($world_key, $land_square['account_key'], 20, 
                $world['land_size'], $land_square['lat'], $land_square['lng'], true);
        }

        // Echo data to client to be parsed
	    if (isset($land_square['land_name'])) {
            // Strip html entities from all untrusted columns, except content as it's stripped on insert
            $land_square['land_name'] = htmlentities($land_square['land_name']);
            $land_square['color'] = htmlentities($land_square['color']);
            $land_square['username'] = htmlentities($land_square['username']);
            if ($json_output) {
                // Filter tags except img with src only
                function filter(&$value) {
                  // $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                  $value = strip_tags($value, '<img>');
                  // $value = preg_replace('#&lt;(/?(?:img))&gt;#', '<\1>', $value);
                  $value = preg_replace("/<([a-z][a-z0-9]*)(?:[^>]*(\ssrc=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/i",'<$1$2$3>', $value);
                  $value = nl2br($value);
                }
                array_walk_recursive($land_square, "filter");
                echo json_encode($land_square);
            }
            else {
                return $land_square;
            }
        }
	    // If none found, default to this
        else {
	        echo '{"error": "Land not found"}';
	    }
	}

	// Land form
	public function land_form()
    {
        // Authentication
        if ($this->session->userdata('logged_in')) {
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
        }
        // If user not logged in, return with fail
        else {
            echo '{"status": "fail", "message": "User not logged in"}';
            return false;
        }
        
		// Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('form_type_input', 'Form Type Input', 'trim|required|max_length[32]|callback_land_form_validation');
        $this->form_validation->set_rules('coord_slug_input', 'Coord Key Input', 'trim|required|max_length[8]');
        $this->form_validation->set_rules('world_key_input', 'World Key Input', 'trim|required|integer|max_length[10]');
        $this->form_validation->set_rules('land_name', 'Land Name', 'trim|max_length[50]');
        $this->form_validation->set_rules('content', 'Content', 'trim|max_length[1000]');

        // Fail
	    if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('failed_form', 'error_block');
            $this->session->set_flashdata('validation_errors', validation_errors());
            if (validation_errors() === '') {
                echo '{"status": "fail", "message": "An unknown error occurred"}';
            }
            echo '{"status": "fail", "message": "'. trim(preg_replace('/\s\s+/', ' ', validation_errors() )) . '"}';
            return false;
        } 
		// Success
        else {
            // Set inputs
            $coord_slug = $this->input->post('coord_slug_input');
            $world_key = $this->input->post('world_key_input');
	        $form_type = $this->input->post('form_type_input');
            $world = $data['world'] = $this->game_model->get_world_by_slug_or_id($world_key);
            $land_square = $this->game_model->get_single_land($world_key, $coord_slug);
            $land_square['sum_effects'] = $this->game_model->get_sum_effects_of_land($land_square['id']);
            $land_key = $land_square['id'];
            $land_name = $this->input->post('land_name');
	        $land_type = $this->input->post('land_type');
            $account = $this->user_model->get_account_by_keys($user_id, $world_key);
            $account = $this->get_full_account($account);
            $account['land_count'] = $this->user_model->get_count_of_account_land($account['id']);
            $account_key = $account['id'];
            $color = $account['color'];
            $content = $this->input->post('content');

            // Content
            // $content = $this->sanitize_html($content);
            $content = $content;

            if ( is_numeric($form_type) ) {
                $action_type = 'build';
            }
            else if ($land_square['land_type'] === $this->unclaimed_key) {
                $action_type = 'claim';
            }
            else if ($account['id'] === $land_square['account_key']) {
                $action_type = 'update';
            }
            else {
                $action_type = 'attack';
            }

            // Check if player is functioning
            if (!$account['functioning']) {
                echo '{"status": "fail", "message": "Your political support is too low for your government to function."}';
                return false;
            }

            // Upgrade Logic
            $effects = $data['modify_effect_dictionary'] = $this->game_model->get_all_modify_effects();
            if ( $action_type === 'build' ) {
                $result = $this->land_form_upgrade($effects, $form_type, $world_key, $account_key, $land_key, $coord_slug);
                if (!$result) {
                    return false;
                }
                echo '{"status": "success", "result": true, "message": "Built"}';
                return true;
            }

            // Do war weariness logic
            if ($action_type === 'attack' || $action_type === 'claim') {
                $war_weariness = $this->war_weariness_calculate($account, $land_square['account_key']);
                $this->game_model->add_war_weariness_to_account($account['id'], $war_weariness);
            }

            // Prevent new players from taking towns or larger
            if ($account['tutorial'] < 2 && $land_square['land_type'] < 3) {
                echo '{"status": "fail", "message": "You must start the game at a village or unclaimed land"}';
                return false;
            }
            // Make capitol if tutorial
            if ($account['tutorial'] < 2) {
                $land_type = $this->town_key;
                $query_action = $this->game_model->update_land_capitol_status($world_key, $coord_slug, true);
                $query_action = $this->user_model->update_account_tutorial($account_key, 2);
                $query_action = $this->game_model->remove_modifiers_from_land($land_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $this->town_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $this->capitol_key);
                // Add town to square
            }
            // Update
            else if ($action_type === 'update') {
                $land_type = $land_square['land_type'];
            // Attack or claim
            } 
            else {
                $land_type = $this->village_key;
                $query_action = $this->game_model->remove_modifiers_from_land($land_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $this->village_key);
            }

            // Tutorial progress for update or build
            if ($account['tutorial'] === '3' && ($action_type === 'update' || $action_type === 'build') ) {
                $query_action = $this->user_model->update_account_tutorial($account_key, 4);
            }
            // Tutorial progress for additional attack or claim
            else if ($account['tutorial'] === '4' && ($action_type === 'attack' || $action_type === 'claim') ) {
                $query_action = $this->user_model->update_account_tutorial($account_key, 5);
            }

            // Update land
	        $query_action = $this->game_model->update_land_data($world_key, $coord_slug, $account_key, $land_name, $content, $land_type, $color);
            
            // Attack response
            if ($action_type === 'attack') {
                echo '{"status": "success", "result": true, "message": "Captured"}';
            }
            // Claim response
            else if ($action_type === 'claim') {
                echo '{"status": "success", "result": true, "message": "Claimed"}';
            } 
            // Update response
            else {
                echo '{"status": "success", "result": true, "message": "Updated"}';
            }

            return true;
	    }
	}

	// Validate Land Form Callback
	public function land_form_validation($form_type_input)
	{
        // User Information
        if (!$this->session->userdata('logged_in')) {
            $this->form_validation->set_message('land_form_validation', 'You are not currently logged in. Please log in again.');
            return false;
        }

        // Get land info for verifying our inputs
        $session_data = $this->session->userdata('logged_in');
        $user_id = $data['user_id'] = $session_data['id'];
        $form_type = $this->input->post('form_type_input');
        $coord_slug = $this->input->post('coord_slug_input');
        $world_key = $this->input->post('world_key_input');
        $world = $data['world'] = $this->game_model->get_world_by_slug_or_id($world_key);
        $active_account = $this->user_model->get_account_by_keys($user_id, $world_key);
        $active_account['land_count'] = $this->user_model->get_count_of_account_land($active_account['id']);
        $active_account_key = $active_account['id'];
        $active_user = $this->user_model->get_user($active_account_key);
        $land_square = $this->game_model->get_single_land($world_key, $coord_slug);
        $name_at_action = $land_square['land_name'];
        $passive_account_key = $land_square['account_key'];
        $passive_user = $this->user_model->get_user($passive_account_key);
        $in_range = $this->check_if_land_is_in_range($world_key, $active_account_key, $active_account['land_count'], $world['land_size'], $land_square['lat'], $land_square['lng'], false);

        // Town, City, Metro, Capitol check

        // Upgrade treasury check

        // Check for inaccuracies
        // Claiming land that isn't unclaimed
        if ($form_type === 'claim' && $land_square['land_type'] != $this->unclaimed_key) {
            $this->form_validation->set_message('land_form_validation', 'This land has been claimed');
            return false;
        }
        // Updating land that isn't theirs
        else if ($form_type === 'update' && $land_square['account_key'] != $active_account_key) {
            $this->form_validation->set_message('land_form_validation', 'This land has been bought and is no longer yours');
            return false;
        }
        // Attacking land that is already theirs
        else if ($form_type === 'attack' && $land_square['account_key'] === $active_account_key) {
            $this->form_validation->set_message('land_form_validation', 'This land is already yours');
            return false;
        } 
        // Attacking or claiming land that is not in range
        else if ( ($form_type === 'claim' || $form_type === 'attack') && !$in_range) {
            $this->form_validation->set_message('land_form_validation', 'This land is not in range');
            return false;
        }

        // Everything checks out
        return true;
	}

    public function land_form_upgrade($effects, $form_type, $world_key, $account_key, $land_key, $coord_slug)
    {
        foreach ($effects as $effect) {
            // Capitol
            if ($effect['name'] === 'capitol' && $form_type === $effect['id']) {
                $query_action = $this->game_model->remove_capitol_from_account($world_key, $account_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $effect['id']);
                $query_action = $this->game_model->update_land_capitol_status($world_key, $coord_slug, $capitol = 1);
                break;
            }
            // Town
            else if ($effect['name'] === 'town' && $form_type === $effect['id']) {
                $query_action = $this->game_model->remove_land_type_modifiers_from_land($land_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $effect['id']);
                $query_action = $this->game_model->upgrade_land_type($coord_slug, $world_key, $this->town_key);
                break;
            }
            // City
            else if ($effect['name'] === 'city' && $form_type === $effect['id']) {
                $query_action = $this->game_model->remove_land_type_modifiers_from_land($land_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $effect['id']);
                $query_action = $this->game_model->upgrade_land_type($coord_slug, $world_key, $this->city_key);
                break;
            }
            // Metropolis
            else if ($effect['name'] === 'metropolis' && $form_type === $effect['id']) {
                $query_action = $this->game_model->remove_land_type_modifiers_from_land($land_key);
                $query_action = $this->game_model->add_modifier_to_land($land_key, $effect['id']);
                $query_action = $this->game_model->upgrade_land_type($coord_slug, $world_key, $this->metropolis_key);
                break;
            }
            // Regular Upgrades
            else if ($form_type === $effect['id']) {
                $query_action = $this->game_model->add_modifier_to_land($land_key, $effect['id']);
                break;
            }
        }
        return true;
    }

    public function war_weariness_calculate($account, $defender_account)
    {
        // Used to make cron more effecient
        if ( !$account['active_account'] ) {
            // Mark account as active
            $this->game_model->update_account_active_state($account['id'], 1);
        }

        // If unclaimed, just 1 war weariness
        if ($defender_account == 0) {
            $war_weariness = 1;
            return $war_weariness;
        }

        // Get accounts
        $defender_account = $this->user_model->get_account_by_id($defender_account);
        $defender_account = $this->get_full_account($defender_account);

        // War Weariness Algorithm
        if ($account['stats']['military_after'] >= $defender_account['stats']['military_after'] * 2) {
            $war_weariness = 1;
        }
        else if ($account['stats']['military_after'] >= $defender_account['stats']['military_after']) {
            $war_weariness = 2;
        }
        else if ($account['stats']['military_after'] * 2 >= $defender_account['stats']['military_after']) {
            $war_weariness = 3;
        }
        else if ($account['stats']['military_after'] * 3 >= $defender_account['stats']['military_after']) {
            $war_weariness = 4;
        } else {
            $war_weariness = 5;
        }
        return $war_weariness;
    }

    // Check if land is in range for account
    public function check_if_land_is_in_range($world_key, $account_key, $land_count, $land_size, $lat, $lng, $siege)
    {
        // All land in range if no land
        if ($land_count < 1) {
            return true;
        }
        // Check surrounding lands
        $coord_array = [];
        if ($siege === false) {
            $coord_array[] = ($lat) . ',' . ($lng);
        }
        $coord_array[] = ($lat + $land_size) . ',' . ($lng);
        $coord_array[] = ($lat) . ',' . ($lng + $land_size);
        $coord_array[] = ($lat - $land_size) . ',' . ($lng);
        $coord_array[] = ($lat) . ',' . ($lng - $land_size);
        // Fix lng crossover point
        foreach ($coord_array as &$coord) {
            if (strpos($coord, '-180') !== false) {
                $coord = str_replace('-180', '180', $coord);
            }
            if (strpos($coord, '182') !== false) {
                $coord = str_replace('182', '-178', $coord);
            }
        }
        $coord_matches = $this->game_model->land_range_check($world_key, $account_key, $coord_array);
        if (!empty($coord_matches) ) {
            return true;
        }
        return false;
    }

    // Get leaderboards
    public function leaderboards($world)
    {
        return true;
/*
        $world_key = $world['id'];
        // Land owned
        $leaderboard_land_owned = $this->leaderboard_model->leaderboard_land_owned($world_key);
        $rank = 1;
        foreach ($leaderboard_land_owned as &$leader) { 
            $leader['rank'] = $rank;
            $leader['account'] = $this->user_model->get_account_by_id($leader['account_key']);
            $leader['user'] = $this->user_model->get_user($leader['account']['user_key']);
            // Math for finding approx land area
            $leader['land_mi'] = number_format($leader['total'] * (70 * $world['land_size']));
            $leader['land_km'] = number_format($leader['total'] * (112 * $world['land_size']));
            $rank++;
        }
        // Return data
        return $leaderboards;
*/
    }

    // Land dictionary for reference
    public function government_dictionary()
    {
        $government_dictionary[0] = 'Anarchy';
        $government_dictionary[1] = 'Democracy';
        $government_dictionary[2] = 'Oligarchy';
        $government_dictionary[3] = 'Autocracy';
        return $government_dictionary;
    }

    public function stroke_color_dictionary()
    {
        $stroke_color_dictionary['village'] = '#428BCA';
        $stroke_color_dictionary['capitol'] = '#FF0000';
        $stroke_color_dictionary['town'] = '#00E300';
        $stroke_color_dictionary['city'] = '#FFD900';
        $stroke_color_dictionary['metropolis'] = '#A600A6';
        return $stroke_color_dictionary;
    }

    // Function to close tags
    public function sanitize_html($html) {
        // Content input allow only gmail whitelisted tags
        $whitelisted_tags = '<a><abbr><acronym><address><area><b><bdo><big><blockquote><br><button><caption><center><cite><code><col><colgroup><dd><del><dfn><dir><div><dl><dt><em><fieldset><font><form><h1><h2><h3><h4><h5><h6><hr><i><img><input><ins><kbd><label><legend><li><map><menu><ol><optgroup><option><p><pre><q><s><samp><select><small><span><strike><strong><sub><sup><table><tbody><td><textarea><tfoot><th><thead><u><tr><tt><u><ul><var>';
        $html = strip_tags($html, $whitelisted_tags);
        // Disallow these character combination to prevent potential javascript injection
        $disallowed_strings = ['onerror', 'onload', 'onclick', 'ondblclick', 'onkeydown', 'onkeypress', 'onkeyup', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup'];
        $html = str_replace($disallowed_strings, '', $html);
        // Close open tags
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
            if (!in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } 
            else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        // Replace new lines with break tags
        $html = preg_replace("/\r\n|\r|\n/",'<br/>',$html);
        // Return result
        return $html;
    }

}