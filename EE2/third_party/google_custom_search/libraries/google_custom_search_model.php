<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Google Custom Search Module Model
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Jesse Bunch
 * @link		http://getbunch.com/
 */

class Google_custom_search_model {
	
	/**
	 * Reference to the EE singleton
	 * @access private
	 * @since 1.0
	 * @var ExpressionEngine
	 */
	private $EE;
	
	// ---------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 * @author Jesse Bunch
	 */
	function Google_custom_search_model() {
		
		$this->EE =& get_instance();
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Returns an array of preferences for this module.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 * @author Jesse Bunch
	 */
	function GetPreferences() {
		
		$this->EE->db->limit(1);
		$objResult = $this->EE->db->get('google_custom_search_preferences');

		if ($objResult->num_rows() !== 1) {

			$this->CreateDefaultPreferences();

			return $this->GetPreferences();

		}

		$arrPreferences = $objResult->row_array();
	
		return $arrPreferences;
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Saves the preference data to the database
	 *
	 * @access public
	 * @since 1.0
	 * @param array $arrData Key/Value pairs corresponding to table columns
	 * @return void
	 * @author Jesse Bunch
	 */
	function SavePreferences($arrData) {
		
		$arrPreferences = $this->GetPreferences();
		
		if (count($arrPreferences)) {
			
			$this->EE->db->where('row_id', $arrPreferences['row_id']);
			$this->EE->db->limit(1);
			$this->EE->db->update('google_custom_search_preferences', $arrData);
			
		}
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Creates the default module preferences.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 * @author Jesse Bunch
	 */
	function CreateDefaultPreferences() {
		
		$this->EE->db->truncate('google_custom_search_preferences');
		
		$this->EE->db->insert('google_custom_search_preferences', array(
			'cache_enabled' => 'y',
			'cache_time' => 30
		));
		
		return $this->EE->db->insert_id();
		
	}
	
	// ---------------------------------------------------------------------
	
}