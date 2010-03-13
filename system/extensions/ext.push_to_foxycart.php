<?php

class Push_to_foxycart {

	var $settings        = array();
	var $name            = 'Push to Foxycart';
	var $version         =  '1.0';
	var $description     = 'Extends ExpressionEngine and the Solspace User Module to push user registrations and edits to Foxycart for SSO';
	var $settings_exist  = 'n';
	var $docs_url        = 'http://ninefour.co.uk/labs';
	
	// Specify your FoxyCart domain and API token
	var $foxy_domain = 'yoursite.foxycart.com';
	var $api_token = 'yourapitoken';
	
	public function Push_to_foxycart($settings='') {

	}
	
	public function activate_extension() {
		global $DB;
		
		$DB->query($DB->insert_string('exp_extensions',
					array(
							'extension_id' => '',
							'class'        => __CLASS__,
							'method'       => "cp_create_member",
							'hook'         => "cp_members_member_create",
							'settings'     => "",
							'priority'     => 10,
							'version'      => $this->version,
							'enabled'      => "y"
						  )
					));
					
		$DB->query($DB->insert_string('exp_extensions',
					array(
							'extension_id' => '',
							'class'        => __CLASS__,
							'method'       => "push_member",
							'hook'         => "user_register_end",
							'settings'     => "",
							'priority'     => 10,
							'version'      => $this->version,
							'enabled'      => "y"
						  )
					));
					
		$DB->query($DB->insert_string('exp_extensions',
					array(
							'extension_id' => '',
							'class'        => __CLASS__,
							'method'       => "edit_member",
							'hook'         => "user_edit_end",
							'settings'     => "",
							'priority'     => 10,
							'version'      => $this->version,
							'enabled'      => "y"
						  )
					));
	}
	
	public function update_extension($current='') {
		global $DB;
		
		if ($current == '' OR $current == $this->version) {
			return false;
		}
		
		return true;
	}
	
	public function disable_extension() {
		global $DB;
		
		$DB->query("DELETE FROM exp_extensions WHERE class = 'Push_to_foxycart'");
	}
	
	public function push_member($User = null, $member_id = null) {
		global $DB;

		//get member data
		$sql = "SELECT * FROM exp_members, exp_member_data
				WHERE exp_members.member_id = exp_member_data.member_id
				AND exp_members.member_id = '".$member_id."'";
		$query = $DB->query($sql);
		$member_data = $query->result[0];
		
		//do domain
		$foxy_domain = $this->foxy_domain;
		
		//do data mappings
		$foxyData = array();
		$foxyData["api_token"] = $this->api_token;
		$foxyData["api_action"] = "customer_save";
		$foxyData["customer_email"] = $member_data['email'];
		$foxyData["customer_password"] = $_POST['password'];
		
		// Amend the ExpressionEngine m_field_id_ values to fit your member profile fields
 		$foxyData['customer_first_name'] = $member_data['m_field_id_37'];
 		$foxyData['customer_last_name'] = $member_data['m_field_id_38'];
 		$foxyData['customer_company'] = $member_data['m_field_id_11'];
 		$foxyData['customer_address1'] = $member_data['m_field_id_1'];
 		$foxyData['customer_address2'] = $member_data['m_field_id_2'];
 		$foxyData['customer_city'] = $member_data['m_field_id_4'];
 		$foxyData['customer_state'] = $member_data['m_field_id_50'];
 		$foxyData['customer_postal_code'] = $member_data['m_field_id_3'];
 		$foxyData['customer_country'] = $member_data['m_field_id_39'];
 		$foxyData['customer_phone'] = $member_data['m_field_id_40'];
 		$foxyData['shipping_first_name'] = $member_data['m_field_id_37'];
 		$foxyData['shipping_last_name'] = $member_data['m_field_id_38'];
 		$foxyData['shipping_company'] = $member_data['m_field_id_11'];
 		$foxyData['shipping_address1'] = $member_data['m_field_id_1'];
 		$foxyData['shipping_address2'] = $member_data['m_field_id_2'];
 		$foxyData['shipping_city'] = $member_data['m_field_id_4'];
 		$foxyData['shipping_state'] = $member_data['m_field_id_50'];
 		$foxyData['shipping_postal_code'] = $member_data['m_field_id_3'];
 		$foxyData['shipping_country'] = $member_data['m_field_id_39'];
 		$foxyData['shipping_phone'] = $member_data['m_field_id_40'];
		
		//echo '<pre>';
		//print_r($foxyData);
		//echo '</pre>';
				
 		//create curl request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://" . $foxy_domain . "/api");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $foxyData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		// If you get SSL errors, you can uncomment the following, or ask your host to add the appropriate CA bundle
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = trim(curl_exec($ch));
 
		curl_close($ch);
		
		/*$foxyResponse = simplexml_load_string($response, NULL, LIBXML_NOCDATA);
		print "<pre>";
		var_dump($foxyResponse);
		print "</pre>";
		exit();*/
	}
	
	public function edit_member($member_id = null, $update_data = null, $cfields = null) {
		$this->push_member(null, $member_id);
	}
	
	public function cp_create_member($member_id, $data) {
		$this->push_member(null, $member_id);
	}
	
}

?>