<?php
defined('BASEPATH')
 OR exit('No direct script access allowed');

Class cron_model extends CI_Model
{
	function increase_support()
	{
		// increment supply by power structure type for all accounts
		// set to 100 when more than 100
	}
	function mark_accounts_as_active()
	{
		// If account tiles > 0, set is_active to true, else set is_active to false
	}
	function census_population()
	{
		// foreach world
		// foreach account
		// set population supply to population of all territories
	}
	function settlement_output()
	{
		$this->db->query("
			UPDATE supply_account_lookup AS sal
			INNER JOIN settlement AS settlement_by_supply ON sal.supply_key = settlement_by_supply.output_supply_key
			INNER JOIN (
				SELECT settlement_key, account_key, COUNT(*) as tile_count
				FROM tile
				GROUP BY settlement_key, account_key
			) AS tile_by_settlement_and_account ON settlement_by_supply.id = tile_by_settlement_and_account.settlement_key AND sal.account_key = tile_by_settlement_and_account.account_key
			SET sal.amount = sal.amount + (tile_by_settlement_and_account.tile_count * settlement_by_supply.output_supply_amount)
		");
	}
	function township_input()
	{
		$accounts = $this->get_all_accounts_for_townships();
		foreach ($accounts as $account) {
			// Starting values
			$grain = $fruit = $vegetables = $livestock = $fish = $coffee = $tea = $cannabis = $alcohols = $tobacco = $energy = $merchandise = $steel = $healthcare = 0;
			$food_needed = $food_paid = $cash_crops_needed = $cash_crops_paid = 0;
			$food_needed += $account['town_count'] * TOWN_FOOD_COST;
			$food_needed += $account['city_count'] * CITY_FOOD_COST;
			$food_needed += $account['metro_count'] * METRO_FOOD_COST;
			$cash_crops_needed += $account['town_count'] * TOWN_CASH_CROPS_COST;
			$cash_crops_needed += $account['city_count'] * CITY_CASH_CROPS_COST;
			$cash_crops_needed += $account['metro_count'] * METRO_CASH_CROPS_COST;
			// Calculate new values
			// Order matters
			if ($account['grain'] >= $food_needed) {
				$grain = $food_needed;
				$food_needed = 0;
			}
			else {
				$grain = 0;
				$food_needed = $food_needed - $account['grain'];
			}
			if ($account['grain'] >= $food_needed) {
				$fruit = $food_needed;
				$food_needed = 0;
			}
			else {
				$fruit = 0;
				$food_needed = $food_needed - $account['fruit'];
			}
			if ($account['grain'] >= $food_needed) {
				$vegetables = $food_needed;
				$food_needed = 0;
			}
			else {
				$vegetables = 0;
				$food_needed = $food_needed - $account['vegetables'];
			}
			if ($account['grain'] >= $food_needed) {
				$livestock = $food_needed;
				$food_needed = 0;
			}
			else {
				$livestock = 0;
				$food_needed = $food_needed - $account['livestock'];
			}
			if ($account['grain'] >= $food_needed) {
				$fish = $food_needed;
				$food_needed = 0;
			}
			else {
				$fish = 0;
				$food_needed = $food_needed - $account['fish'];
			}
			if ($account['coffee'] >= $food_needed) {
				$coffee = $cash_crops_needed;
				$cash_crops_needed = 0;
			}
			else {
				$coffee = 0;
				$cash_crops_needed = $cash_crops_needed - $coffee;
			}
			if ($account['tea'] >= $food_needed) {
				$tea = $cash_crops_needed;
				$cash_crops_needed = 0;
			}
			else {
				$tea = 0;
				$cash_crops_needed = $cash_crops_needed - $tea;
			}
			if ($account['cannabis'] >= $food_needed) {
				$cannabis = $cash_crops_needed;
				$cash_crops_needed = 0;
			}
			else {
				$cannabis = 0;
				$cash_crops_needed = $cash_crops_needed - $cannabis;
			}
			if ($account['alcohols'] >= $food_needed) {
				$alcohols = $cash_crops_needed;
				$cash_crops_needed = 0;
			}
			else {
				$alcohols = 0;
				$cash_crops_needed = $cash_crops_needed - $alcohols;
			}
			if ($account['tobacco'] >= $food_needed) {
				$tobacco = $cash_crops_needed;
				$cash_crops_needed = 0;
			}
			else {
				$tobacco = 0;
				$cash_crops_needed = $cash_crops_needed - $tobacco;
			}
			$energy = ($account['town_count'] * TOWN_ENERGY_COST) + ($account['city_count'] * CITY_ENERGY_COST) + ($account['metro_count'] * METRO_ENERGY_COST);
			$merchandise = ($account['town_count'] * TOWN_MERCHANDISE_COST) + ($account['city_count'] * CITY_MERCHANDISE_COST) + ($account['metro_count'] * METRO_MERCHANDISE_COST);
			$steel = ($account['town_count'] * TOWN_STEEL_COST) + ($account['city_count'] * CITY_STEEL_COST) + ($account['metro_count'] * METRO_STEEL_COST);
			$healthcare = ($account['town_count'] * TOWN_HEALTHCARE_COST) + ($account['city_count'] * CITY_HEALTHCARE_COST) + ($account['metro_count'] * METRO_HEALTHCARE_COST);
			// Apply new values
			$data = [
				[
					'account_key' => $account['id'],
					'supply_key' => GRAIN_KEY,
					'amount' => $account['grain'] - $grain,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => FRUIT_KEY,
					'amount' => $account['fruit'] - $fruit,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => VEGETABLES_KEY,
					'amount' => $account['vegetables'] - $vegetables,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => LIVESTOCK_KEY,
					'amount' => $account['livestock'] - $livestock,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => FISH_KEY,
					'amount' => $account['fish'] - $fish,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => COFFEE_KEY,
					'amount' => $account['coffee'] - $coffee,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => TEA_KEY,
					'amount' => $account['tea'] - $tea,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => CANNABIS_KEY,
					'amount' => $account['cannabis'] - $cannabis,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => ALCOHOLS_KEY,
					'amount' => $account['alcohols'] - $alcohols,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => TOBACCO_KEY,
					'amount' => $account['tobacco'] - $tobacco,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => ENERGY_KEY,
					'amount' => $account['energy'] - $energy,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => MERCHANDISE_KEY,
					'amount' => $account['merchandise'] - $merchandise,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => STEEL_KEY,
					'amount' => $account['steel'] - $steel,
				],
				[
					'account_key' => $account['id'],
					'supply_key' => HEALTHCARE_KEY,
					'amount' => $account['healthcare'] - $healthcare,
				],
			];
			// Run update
			$this->db->where('account_key', $account['id']);
			$this->db->update_batch('supply_account_lookup',$data,'supply_key');
		}
	}
	function get_all_accounts_for_townships()
	{
	    $this->db->select("
	    	*,
	    	(SELECT COUNT(tile.id) FROM tile WHERE account_key = account.id AND settlement_key = " . TOWN_KEY . ") AS town_count,
	    	(SELECT COUNT(tile.id) FROM tile WHERE account_key = account.id AND settlement_key = " . CITY_KEY . ") AS city_count,
	    	(SELECT COUNT(tile.id) FROM tile WHERE account_key = account.id AND settlement_key = " . METRO_KEY . ") AS metro_count,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . GRAIN_KEY . ") AS grain,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . FRUIT_KEY . ") AS fruit,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . VEGETABLES_KEY . ") AS vegetables,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . LIVESTOCK_KEY . ") AS livestock,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . FISH_KEY . ") AS fish,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . COFFEE_KEY . ") coffee,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . TEA_KEY . ") AS tea,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . CANNABIS_KEY . ") AS cannabis,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . ALCOHOLS_KEY . ") AS alcohols,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . TOBACCO_KEY . ") AS tobacco,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . ENERGY_KEY . ") AS energy,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . MERCHANDISE_KEY . ") AS merchandise,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . STEEL_KEY . ") AS steel,
	    	(SELECT amount FROM supply_account_lookup WHERE account_key = account.id AND supply_key = " . HEALTHCARE_KEY . ") AS healthcare,
    	");
	    $this->db->from('account');
	    $this->db->where('account.is_active', true);
	    $query = $this->db->get();
	    $result = $query->result_array();
	    return $result;
	}
	function industry_output_input()
	{
		// foreach world
		// foreach account
		// foreach settlement that has a township ordered by population desc
		// if does not have all inputs, mark is_insufficient_industry_input
		// subtract from supply the inputs
		// create outputs
	}
	function income_collect()
	{
		// foreach world
		// foreach account
		// get sum settlement GDP where not insufficient_input
		// get sum industry GDP where not insufficient_input
		// get taxed income
		// get subtract corruption
		// apply earnings
	}
	function update_cache_leaderboards()
	{
		// foreach supply
		// get top 100 accounts by that supply
		// generate datetime plus world id identified json
	}
    function world_resets()
    {
        $force_reset_world_id = isset($_GET['world_id']) ? $_GET['world_id'] : false;
        $now = date('Y-m-d H:i:s');
        $worlds = $this->game_model->get_all('world');
        foreach ($worlds as $world) {
            // Check if it's time to run
            $time_to_reset = parse_crontab($now, $world['crontab']);
            if (!$time_to_reset && !$force_reset_world_id) {
                continue;
            }
            if ($force_reset_world_id) {
                if ($force_reset_world_id != $world['id']) {
                    continue;
                }
            }
            echo 'Resetting world ' . $world['id'] . ' - ' . $world['slug'] . ' - ';
            // $this->backup();
            // $this->reset_tiles();
            // $this->reset_trades();
            // $this->reset_accounts();
            $this->regenerate_resources($world['id']);
        }
    }
	function regenerate_resources($world_key)
	{
		$this->reset_resources($world_key);
		$resources = $this->game_model->get_all('resource');
		foreach ($resources as $resource) {
			$this->resource_distribute($resource);
			$this->db->where('lat > ', LOWEST_LAT_RESOURCE_GEN);
			$this->db->where('resource_key', NULL);
			$this->db->order_by('RAND()');
			$this->db->limit($resource['frequency_per_world']);
			$data = array(
				'resource_key' => $resource['id'],
			);
			$this->db->update('tile', $data);
		}
	}
	function reset_resources($world_key)
	{
		$data = array(
			'resource_key' => null,
		);
		$this->db->update('tile', $data);
	}
	function resource_distribute($resource)
	{
		$this->db->group_start();
		// Light bias against barren
		if ($resource['spawns_in_barren'] && mt_rand(0,10) > BARREN_BIAS) {
			$this->db->where('terrain_key', BARREN_KEY);
		}
		if ($resource['spawns_in_mountain']) {
			$this->db->or_where('terrain_key', MOUNTAIN_KEY);
		}
		// Strong bias against tundra
		if ($resource['spawns_in_tundra'] && mt_rand(0,10) > TUNDRA_BIAS) {
			$this->db->or_where('terrain_key', TUNDRA_KEY);
		}
		if ($resource['spawns_in_coastal']) {
			$this->db->or_where('terrain_key', COASTAL_KEY);
		}
		$this->db->group_end();
	}
}