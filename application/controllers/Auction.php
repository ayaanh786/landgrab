<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class Auction extends CI_Controller {

	function __construct() {
	    parent::__construct();
        $this->load->model('game_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
        $this->load->model('transaction_model', '', TRUE);
	}

	// Game view and update json
	public function new_auction()
	{
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('coord_slug', 'Coord Slug', 'trim|required|max_length[8]|callback_new_auction_validation');
        $this->form_validation->set_rules('world_key', 'World Key', 'trim|required|integer|max_length[10]');

        // Fail
        if ($this->form_validation->run() == FALSE) {

            // Set Fail Errors
            $this->session->set_flashdata('failed_form', 'error_block');
            $this->session->set_flashdata('validation_errors', validation_errors());
            if (validation_errors() === '') {
                echo '{"status": "fail", "message": "An unknown error occurred"}';
            }

            // Return to game as failure with new lines removed
            echo '{"status": "fail", "message": "'. trim(preg_replace('/\s\s+/', ' ', validation_errors() )) . '"}';
            return false;

        // Success
        } else {
            // Return to game as success
            echo '{"status": "success"}';
            return true;
        }
	}

    // Validate new auction request
    public function new_auction_validation()
    {
        // User Information
        if ($this->session->userdata('logged_in')) {
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
        }

        // Get Data
        $coord_slug = $this->input->post('coord_slug');
        $world_key = $this->input->post('world_key');
        $buyer_account = $this->user_model->get_account_by_keys($user_id, $world_key);
        $buyer_account_key = $buyer_account['id'];
        $buyer_user = $this->user_model->get_user($buyer_account_key);
        $land_square = $this->game_model->get_single_land($world_key, $coord_slug);
        $amount = 1000;
        $new_buying_owner_cash = $buyer_account['cash'] - $amount;
        $buyer_account_key = $buyer_account['id'];

        // Check that this is the proper owner
        if ($buyer_account_key != $land_square['account_key']) {
            return false;
        }

        // Apply charge for auction
        $query_action = $this->game_model->update_account_cash_by_account_id($buyer_account_key, $new_buying_owner_cash);

        // Add as new auction
        $query_action = $this->game_model->new_auction($coord_slug, $world_key);

        return true;
    }

}