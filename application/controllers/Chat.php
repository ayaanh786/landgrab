<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('America/New_York');

class Chat extends CI_Controller {

	function __construct() {
	    parent::__construct();

        // force_ssl();
        
        $this->load->model('chat_model', '', TRUE);
        $this->load->model('user_model', '', TRUE);
	}

	// Load chats
	public function load()
	{
        // Set parameters
        $world_key = $this->input->post('world_key');
        $last_message_id = $this->input->post('last_message_id');
        $inital_load = $this->input->post('inital_load') === 'true' ? true : false;
        $limit = 50;

        // Get chats and reverse array

        // Get messages
        if ($inital_load) {
            $chats = $this->chat_model->load_chat_by_limit($world_key, $limit);
            $chats = array_reverse($chats);
        }
        else {
            $chats = $this->chat_model->load_message_by_last_message_id($world_key, $last_message_id);
        }
        
        echo json_encode($chats);
    }

    // For new chats
    public function new_chat()
    {                
        // Validation
        $this->load->library('form_validation');
        $this->form_validation->set_rules('world_key', 'World Key', 'trim|required|integer|max_length[10]|callback_new_chat_validation');
        $this->form_validation->set_rules('chat_input', 'Chat Message', 'trim|required|max_length[250]');

        // Fail Validation
        if ($this->form_validation->run() == FALSE) {
            echo trim(strip_tags(validation_errors()));
            return false;
        }

        // Set variables
        $world_key = $_POST['world_key'];
        $account = $this->user_model->this_account($world_key);
        $username = $account['username'];
        $color = $account['color'];
        $message = htmlspecialchars($_POST['chat_input']);

        // Insert chat
        $this->chat_model->new_chat($account['user_id'], $account['id'], $username, $color, $message, $world_key);
    }

    // New Chat Callback
    public function new_chat_validation()
    {
        // Authentication
        $log_check = $data['log_check'] = $data['user_id'] = false;
        if ($this->session->userdata('user')) {
            $log_check = $data['log_check'] = true;
            $session_data = $this->session->userdata('logged_in');
            $user_id = $data['user_id'] = $session_data['id'];
        }
        else {
            return false;
        }
        
        // Limit number of new chats in a timespan
        $chat_limit_amount = 8;
        $chat_limit_length = 60;
        $recent_chats = $this->chat_model->recent_chats($user_id, $chat_limit_length);
        if ($recent_chats > $chat_limit_amount) {
            echo 'Your talking too much';
            return false;
        }

        return true;
    }

}