<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Use this where needed for debugging
    // echo '<br>' . $this->db->last_query() . '<br>';

Class game_model extends CI_Model
{
 // Get world by id
 function get_world($world_id)
 {
    $this->db->select('*');
    $this->db->from('world');
    $this->db->where('id', $world_id);
    $query = $this->db->get();
    $result = $query->result_array();
    return isset($result[0]) ? $result[0] : false;
 }
 // Get world by slug
 function get_world_by_slug_or_id($slug)
 {
    $this->db->select('*');
    $this->db->from('world');
    $this->db->where('slug', $slug);
    $this->db->or_where('id', $slug);
    $this->db->limit(1);
    $query = $this->db->get();
    $result = $query->result_array();
    return isset($result[0]) ? $result[0] : false;
 }
 // Get all lands
 function get_all_lands_in_world($world_key)
 {
    $this->db->select('*');
    $this->db->from('land');
    $this->db->where('world_key', $world_key);
    $query = $this->db->get();
    return $query->result_array();
 }
 // Get all lands where claimed
 function get_all_lands_in_world_where_claimed($world_key)
 {
    $this->db->select('*');
    $this->db->from('land');
    $this->db->where('world_key', $world_key);
    $this->db->where('claimed', 1);
    $query = $this->db->get();
    return $query->result_array();
 }
 // Get single land
 function get_single_land($world_key, $coord_slug)
 {
    $this->db->select('*');
    $this->db->from('land');
    $this->db->where('coord_slug', $coord_slug);
    $this->db->where('world_key', $world_key);
    $query = $this->db->get();
    $result = $query->result_array();
    return isset($result[0]) ? $result[0] : false;
 }
 // Update land data
 function update_land_data($world_key, $claimed, $coord_slug, $lat, $lng, $account_key, $land_name, $price, $content, $primary_color)
 {
    $data = array(
        'claimed' => $claimed,
        'coord_slug' => $coord_slug,
        'lat' => $lat,
        'lng' => $lng,
        'account_key' => $account_key,
        'land_name' => $land_name,
        'price' => $price,
        'content' => $content,
        'primary_color' => $primary_color
    );
    $this->db->where('coord_slug', $coord_slug);
    $this->db->where('world_key', $world_key);
    $this->db->update('land', $data);
    return true;
 }
 // Update cash in account
 function update_account_cash_by_account_id($account_id, $cash)
 {
    // Seller add cash
    $data = array(
        'cash' => $cash
    );
    $this->db->where('id', $account_id);
    $this->db->update('account', $data);
    return true;
 }
 // Forfeit all land of account
 function forfeit_all_land_of_account($account_id, $price)
 {
    $data = array(
        'claimed' => 0,
        'account_key' => 0,
        'price' => $price
    );
    $this->db->where('account_key', $account_id);
    $this->db->update('land', $data);
    return true;
 }
 // Get projected tax
 function get_sum_and_count_of_account_land($account_id)
 {
    $this->db->select('SUM(price) as sum, COUNT(*) as count');
    $this->db->from('land');
    $this->db->where('account_key', $account_id);
    $query = $this->db->get();
    $result = $query->result_array();
    return isset($result[0]) ? $result[0] : 0;
 }
 // Record most recent rebate
 function record_most_recent_rebate($latest_rebate, $world_key)
 {
    $data = array(
        'latest_rebate' => $latest_rebate
    );
    $this->db->where('id', $world_key);
    $this->db->update('world', $data);
    return true;
 }
 // Market Order Select
 function market_order_select($world_key, $account_key, $max_lands, $max_price, $min_lat, $max_lat, $min_lng, $max_lng)
 {
    $this->db->select('*');
    $this->db->from('land');
    $this->db->where('world_key', $world_key);
    $this->db->where('price <=', $max_price);
    $this->db->where('lat >=', $min_lat);
    $this->db->where('lat <=', $max_lat);
    $this->db->where('lng >=', $min_lng);
    $this->db->where('lng <=', $max_lng);
    $this->db->where('account_key !=', $account_key);
    $this->db->order_by('price', 'ASC');
    $this->db->order_by('id', 'random');
    $this->db->limit($max_lands);
    $query = $this->db->get();
    return $query->result_array();
 }

}
?>