<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Google Custom Search Module Install/Update File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Jesse Bunch
 * @link		http://getbunch.com/
 */

class Google_custom_search_upd {
	
	var $version        = '1.2.2'; 
	var $module_name = "Google_custom_search";
	
	/**
	 * Constructor
	 */
	public function __construct() {
		
		$this->EE =& get_instance();
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install() {
		
		$data = array(
			'module_name' 	 => $this->module_name,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);

		$this->EE->db->insert('modules', $data);
		
		$this->EE->load->dbforge();
		
		$google_custom_search_preferences_fields = array(
		    'row_id' => array(
		        'type' => 'int',
		        'constraint' => '10',
		        'null' => FALSE),
		    'api_key' => array(
		        'type' => 'varchar',
		        'constraint' => '150',
		        'null' => FALSE),
		    'custom_search_id' => array(
		        'type' => 'varchar',
		        'constraint' => '150',
		        'null' => FALSE),
		    'cache_enabled' => array(
		        'type' => 'varchar',
		        'constraint' => '1',
		        'null' => FALSE,
				  'default' => 'y'),
		    'cache_time' => array(
		        'type' => 'int',
		        'constraint' => '5',
		        'null' => FALSE,
				  'default' => 30)
		);
		
		$this->EE->dbforge->add_field($google_custom_search_preferences_fields);
		$this->EE->dbforge->add_key('row_id', TRUE);
		$this->EE->dbforge->create_table('google_custom_search_preferences');
		
		$this->EE->load->library('Google_custom_search_model');
		$this->EE->google_custom_search_model->CreateDefaultPreferences();
		
		
		return TRUE;
		
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall() {
		
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => $this->module_name));
		
		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');
		
		$this->EE->db->where('module_name', $this->module_name);
		$this->EE->db->delete('modules');
		
		$this->EE->db->where('class', $this->module_name);
		$this->EE->db->delete('actions');
		
		$this->EE->db->where('class', $this->module_name.'_mcp');
		$this->EE->db->delete('actions');
		
		$this->EE->load->dbforge();
		$this->EE->dbforge->drop_table('google_custom_search_preferences');
		
		return TRUE;
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '') {
		
		// If you have updates, drop 'em in here.
		return TRUE;
		
	}
	
}
/* End of file upd.google_custom_search.php */
/* Location: /system/expressionengine/third_party/google_custom_search/upd.google_custom_search.php */