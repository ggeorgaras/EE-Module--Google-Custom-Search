<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Google Custom Search Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Jesse Bunch
 * @link		http://getbunch.com/
 */

class Google_custom_search {
	
	/**
	 * Useless EE variable.
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $return_data;
	
	// ---------------------------------------------------------------------
	
	/**
	 * Constructor, initializes libraries
	 */
	public function __construct() {
		
		$this->EE =& get_instance();
		
		$this->EE->load->library('google_custom_search_model');
		
	}
	
	// ----------------------------------------------------------------

	/**
	 * Main search method. Returns HTML for output.
	 * 
	 * Allowed Parameters:
	 *  - query: the text query to search for
	 *  - query_param: a post variable to look for (overrides query)
	 *  - offset: the result index to start with
	 *  - limit: the maximum number of results 1-10 (default: 10)
	 *  - safe_search_mode: off, medium, or high (default: off)
	 *
	 * @example /examples/search.html
	 * @access public
	 * @since 1.0
	 * @return string
	 * @author Jesse Bunch
	 */
	public function search() {
		
		// -------------------------------------
		//  Fetch Prefs
		// -------------------------------------
		
		$arrPrefs = $this->EE->google_custom_search_model->GetPreferences();
		
		// -------------------------------------
		//  Fetch Params
		// -------------------------------------
		
		$strQuery = $this->EE->TMPL->fetch_param('query');
		$strQueryParam = $this->EE->TMPL->fetch_param('query_param', 'q');
		$intOffset = $this->EE->TMPL->fetch_param('offset', 1);
		$intLimit = $this->EE->TMPL->fetch_param('limit', 10);
		$strSafeSearchMode = $this->EE->TMPL->fetch_param('safe_search_mode', 'off');
		
		// -------------------------------------
		//  Check for query post variable (overrides query param)
		// -------------------------------------
		
		if ($this->EE->input->post($strQueryParam)) {
			$strQuery = $this->EE->input->post($strQueryParam);
		}
		
		// -------------------------------------
		//  Do the search
		// -------------------------------------

		if (!class_exists('GoogleSearcher')) {
			require(PATH_THIRD . '/google_custom_search/libraries/google_searcher.php');
		}
		
		$objResults = GoogleSearcher::DoSearch($arrPrefs['api_key'], $arrPrefs['custom_search_id'], $strQuery, $intLimit, $intOffset);
		
		if ($objResults->LastError()) {
			$this->_template_log($objResults->LastError());
		}
		
		// -------------------------------------
		//  No Results?
		// -------------------------------------
		
		if (!count($objResults->arrResults['results'])) {
			
			$strNoResultsContent = $this->EE->TMPL->no_results();
			$strSpellingSuggestion = (isset($objResults->arrResults['spelling']['correctedQuery'])) ? $objResults->arrResults['spelling']['correctedQuery'] : '';
			
			$arrVars = array(
				'error_text' => $objResults->LastError(),
				'spelling_suggestion' => $strSpellingSuggestion,
				'url_safe_spelling_suggestion' => rawurlencode($strSpellingSuggestion),
				'query' => $strQuery
			);
			
			return $this->EE->TMPL->parse_variables($strNoResultsContent, array($arrVars));
				
		}
		
		// -------------------------------------
		//  Parse the vars
		// -------------------------------------
		
		$arrVars = $this->_ParseEEVars($objResults);
		$strTemplate = $this->EE->TMPL->tagdata;
		
		return $this->EE->TMPL->parse_variables($strTemplate, $arrVars);
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Parses results into variables for the EE template parser
	 *
	 * @access private
	 * @since 1.0
	 * @param GoogleSearcher $objResults By Referencer
	 * @return array
	 * @author Jesse Bunch
	 */
	private function _ParseEEVars(&$objResults) {
		
		$arrResults = $objResults->arrResults;
		$intTotalResults = $arrResults['cursor']['estimatedResultCount'];
		$intTotalPages = count($arrResults['cursor']['pages']);
		$intResultsThisPage = count($arrResults['results']);
		$intCurrentPage = $arrResults['cursor']['currentPageIndex'];
		$intCurrentPageOffset = $arrResults['cursor']['pages'][$intCurrentPage]['start'];
		$intNextPage = $intCurrentPage + 1;
		$intPrevPage = $intCurrentPage - 1;
		$intLastPage = $intTotalPages - 1;
		$intFirstPage = 0;
		$isNextPage = (isset($arrResults['cursor']['pages'][$intNextPage]));
		$isPrevPage = (isset($arrResults['cursor']['pages'][$intPrevPage]));
		$isLastPage = (isset($arrResults['cursor']['pages'][$intLastPage]) && ($intLastPage != $intCurrentPage));
		$isFirstPage = ($intCurrentPage != 0);
		$intNextPageOffset = ($isNextPage) ? $arrResults['cursor']['pages'][$intNextPage]['start'] : 0;
		$intPrevPageOffset = ($isPrevPage) ? $arrResults['cursor']['pages'][$intPrevPage]['start'] : 0;
		$intLastPageOffset = ($isLastPage) ? $arrResults['cursor']['pages'][$intLastPage]['start'] : 0;
		$intFirstPageOffset = 0;
		
		// -------------------------------------
		//  Standardize Results Variable Naming
		// -------------------------------------

		foreach($arrResults['results'] as &$arrResult) {
			
			$arrResult = array(
				'unescaped_url' => $arrResult['unescapedUrl'],
				'url' => $arrResult['url'],
				'visible_url' => $arrResult['visibleUrl'],
				'cache_url' => $arrResult['cacheUrl'],
				'title' => $arrResult['title'],
				'unformatted_title' => $arrResult['titleNoFormatting'],
				'content' => $arrResult['content'],
				'unformatted_content' => strip_tags($arrResult['content'])
			);
			
		}
		
		// -------------------------------------
		//  Create Pagination Variable Pair
		// -------------------------------------
		
		$arrPagination = array();
		
		foreach($arrResults['cursor']['pages'] as $intPageIndex => $arrPage) {
			$arrPagination[] = array(
				'offset' => $arrPage['start'],
				'label' => $arrPage['label'],
				'is_active' => ($intCurrentPage == $intPageIndex),
				'is_last_page' => ($intPageIndex == ($intTotalPages - 1))
			);
		}
		
		// -------------------------------------
		//  Create EE Variables Array
		// -------------------------------------
		
		$arrVars = array(
			'query' => $objResults->strQuery,
			'url_safe_query' => rawurlencode($objResults->strQuery),
			'total_results' => $arrResults['cursor']['estimatedResultCount'],
			'total_this_page' => count($arrResults['results']),
			'is_next_page' => $isNextPage,
			'is_prev_page' => $isPrevPage,
			'is_last_page' => $isLastPage,
			'is_first_page' => $isFirstPage,
			'next_page_offset' => $intNextPageOffset,
			'prev_page_offset' => $intPrevPageOffset,
			'last_page_offset' => $intLastPageOffset,
			'first_page_offset' => $intFirstPageOffset,
			'safe_search_mode' => $objResults->strSafeSearch,
			'start_index' => $intCurrentPageOffset + 1,
			'last_index' => $intCurrentPageOffset + $intResultsThisPage,
			'pagination' => count($arrPagination) ? $arrPagination : FALSE,
			'results' => $arrResults['results']
		);
		
		// var_dump($arrVars);
		// exit;
		
		return array($arrVars);
		
	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Helper function for logging items to the template log
	 *
	 * @access private
	 * @since 1.0
	 * @param string $strMessage 
	 * @return void
	 * @author Jesse Bunch
	 */	
	private function _template_log($strMessage) {
		
		$this->EE->TMPL->log_item("Google Custom Search Module: $strMessage");	
			
	}
	
	// ---------------------------------------------------------------------
	
	
}
/* End of file mod.google_custom_search.php */
/* Location: /system/expressionengine/third_party/google_custom_search/mod.google_custom_search.php */