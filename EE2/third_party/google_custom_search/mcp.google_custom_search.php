<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Google Custom Search Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Jesse Bunch
 * @link		http://getbunch.com/
 */

class Google_custom_search_mcp {
	
	/**
	 * Name of the module
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	var $module_name = "google_custom_search";
	
	/**
	 * URL base for the module's links
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $_strBase;
	
	/**
	 * URL base for the module's forms
	 * @access private
	 * @since 1.0
	 * @var string
	 */			
	private $_strFormBase;
	
	/**
	 * A message to be shown to the user. Flashdata.
	 * @access private
	 * @since 1.0
	 * @var string
	 */
	private $_strMessage;

	// ---------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 * @param boolean $switch 
	 * @return void
	 * @author Jesse Bunch
	 */
	function Google_custom_search_mcp($switch = TRUE) {
		
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance(); 
		$this->_strBase	 	 = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		$this->_strFormBase = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
		
		// Initialize helpers
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->load->helper('form');
		
		$this->EE->load->library('google_custom_search_model');
		
	}
	
	// ---------------------------------------------------------------------

	/**
	 * Module landing page
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 * @author Jesse Bunch
	 */
	function index() {
		
		$arrVars = array();
		$arrVars['arrSettings'] = $this->EE->google_custom_search_model->GetPreferences();
		return $this->_LoadView('preferences', 'preferences', $arrVars);
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Preferences save routine
	 *
	 * @access public
	 * @since 1.0
	 * @return string
	 * @author Jesse Bunch
	 */
	function SetPreferences_Submit() {
		
		$arrData = $this->EE->input->post('data');
		
		if (is_array($arrData)) {
			
			$this->EE->google_custom_search_model->SavePreferences($arrData);
			
		}
		
		$this->_strMessage = lang('preferences_saved');
		
		return $this->index();
		
	}

	// ---------------------------------------------------------------------
	
	/**
	 * Helper method for loading views. Takes care of some repetitive tasks.
	 *
	 * @access private
	 * @since 1.0
	 * @param string $strContentView The view file name to load
	 * @param string $strLangKey The language key item to use as the title
	 * @param array $arrVars An array of template vars to pass along
	 * @return string
	 * @author Jesse Bunch
	 */
	private function _LoadView($strContentView, $strLangKey, $arrVars = array()) {
		
		$arrVars['_strContentView'] = $strContentView;
		$arrVars['_strBase'] = $this->_strBase;
		$arrVars['_strFormBase'] = $this->_strFormBase;
		
		$arrVars['_strMessage'] = $this->_strMessage;
		$this->_strMessage = '';
		
		$this->EE->cp->set_variable('cp_page_title', lang($strLangKey));
		$this->EE->cp->set_breadcrumb($this->_strBase, lang('google_custom_search_module_name'));

		return $this->EE->load->view('_wrapper', $arrVars, TRUE);
		
	}
	
	// ---------------------------------------------------------------------
	
}
/* End of file mcp.google_custom_search.php */
/* Location: /system/expressionengine/third_party/google_custom_search/mcp.google_custom_search.php */