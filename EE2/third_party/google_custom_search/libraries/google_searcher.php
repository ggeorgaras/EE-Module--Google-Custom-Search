<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Google Custom Searcher Class
* 
* Example:
* <code>
* <?php
* 		$objResults = GoogleSearcher::DoSearch('apikey, 'searchengineid', 'myquery', 10, 0);
* ?>
* </code>
*
* @package		ExpressionEngine
* @subpackage	Addons
* @category	Module
* @author		Jesse Bunch
* @link		http://getbunch.com/
* @link http://code.google.com/apis/websearch/docs/reference.html#_fonje_urlbase
*/

class GoogleSearcher {
	
	/**
	 * The Google API URL Endpoint
	 */
	const API_URL = 'https://ajax.googleapis.com/ajax/services/search/web';

	/**
	 * The query sent to Google
	 * @access public
	 * @var string
	 * @since 1.0
	 */
	public $strQuery;

	/**
	 * Maximum results to be returned. Must be either 1-8 or 'filtered_cse'
	 * @access public
	 * @var mixed
	 * @since 1.0
	 */
	public $intLimit;

	/**
	 * The start index to grab results for
	 * @access public
	 * @var integer
	 * @since 1.0
	 */
	public $intOffset;

	/**
	 * Safe search preference.
	 * @access public
	 * @see GoogleSafeSearch
	 * @var GoogleSafeSearch
	 * @since 1.0
	 */
	public $strSafeSearch;

	/**
	 * The user's API key from Google
	 * @access public
	 * @var string
	 * @since 1.0
	 */
	public $strAPIKey;

	/**
	 * The user's unique search engine ID
	 * @access public
	 * @var string
	 * @since 1.0
	 */
	public $strSearchEngineID;
	
	/**
	 * The raw response from Google's servers
	 * @access public
	 * @var string
	 * @since 1.0
	 */
	public $strResponse;
	
	/**
	 * The response details. Usually, this contains
	 * an error message of some sort.
	 * @access public
	 * @var string
	 * @since 1.0
	 */
	public $strResponseDetails;
	
	/**
	 * The HTTP response status code
	 * @access public
	 * @var integer
	 * @since 1.0
	 */
	public $intStatusCode;
	
	/**
	 * The array of results from Google
	 * @access public
	 * @var array
	 * @since 1.0
	 */
	public $arrResults;

	/**
	 * An array of errors that have occurred
	 * @access public
	 * @var array
	 * @since 1.0
	 */
	public $arrErrors;	

	// ---------------------------------------------------------------------

	/**
	 * Constructor. Nothin' doin'
	 * @author Jesse Bunch
	 */
	private function __construct() {}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Returns the last error that has occurred. If no error has occurred,
	 * this method returns FALSE.
	 *
	 * @access public
	 * @since 1.0
	 * @return mixed
	 * @author Jesse Bunch
	 */
	public function LastError() {
		
		if (empty($this->arrErrors)) {
			return FALSE;
		} else {
			return $this->arrErrors[count($this->arrErrors) - 1];
		}
		
	}

	// ---------------------------------------------------------------------

	/**
	 * Performs validation on the class variables prior to searching
	 *
	 * @access private
	 * @since 1.0
	 * @return boolean Is everything OK?
	 * @author Jesse Bunch
	 */
	private function _IsSearchValid() {

		// There must be a query
		if (empty($this->strQuery)) {
			$this->arrErrors[] = 'The query was empty';
		}

		// There must be an API Key
		if (empty($this->strAPIKey)) {
			$this->arrErrors[] = 'The API key was empty';
		}

		// There must be a query
		if (empty($this->strSearchEngineID)) {
			$this->arrErrors[] = 'The search engine ID was empty';
		}

		// The limit must be between 1-8 or 'filtered_cse'
		if ((!is_numeric($this->intLimit) && $this->intLimit != 'filtered_cse') || (is_numeric($this->intLimit) && $this->intLimit > 8 || $this->intLimit < 1)) {
			$this->arrErrors[] = 'An invalid limit was specified.';
		}

		// The offset must be between 1 and (101 - intLimit)
		if (!is_numeric($this->intOffset) || $this->intOffset < 0) {
			$this->arrErrors[] = 'An invalid offset was specified.';
		}

		// How'd we do?
		if (empty($this->arrErrors)) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	// ---------------------------------------------------------------------

	/**
	 * Performs the cURL GET to Google's servers
	 *
	 * @access private
	 * @since 1.0
	 * @param string $strURL The url to fetch data from
	 * @param array $arrParams URL parameters to send in the query string
	 * @return string The response
	 * @author Jesse Bunch
	 */
	private function _FetchData($strURL, $arrParams = array()) {
		
		// var_dump($arrParams);

		$objCurlHandle = curl_init();

		curl_setopt($objCurlHandle, CURLOPT_URL, $strURL . '?' . http_build_query($arrParams));;
		curl_setopt($objCurlHandle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($objCurlHandle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($objCurlHandle, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($objCurlHandle, CURLOPT_FAILONERROR, FALSE);

		$strResponse = curl_exec($objCurlHandle);
		curl_close($objCurlHandle);
		
		// var_dump($strResponse);
		// exit;
		
		return $strResponse;

	}
	
	// ---------------------------------------------------------------------
	
	/**
	 * Processes the string result from Google, converting it to an array
	 *
	 * @access private
	 * @since 1.0
	 * @param string $strResponse 
	 * @return array
	 * @author Jesse Bunch
	 */
	private function _ProcessResults($strResponse) {
		
		$arrResults = json_decode($strResponse, TRUE);
		
		if (json_last_error()) {
			
			$this->arrErrors[] = json_last_error();
			return FALSE;
			
		}
		
		$this->intStatusCode = $arrResults['responseStatus'];
		$this->strResponseDetails = $arrResults['responseDetails'];
		
		return $arrResults['responseData'];
		
	}

	// ---------------------------------------------------------------------

	/**
	 * This function performs the search and returns
	 * a new instance of the class containing all the pertinent info.
	 *
	 * @static 
	 * @since 1.0
	 * @param string $strAPIKey 
	 * @param string $strSearchEngineID 
	 * @param string $strQuery 
	 * @param integer $intLimit 
	 * @param integer $intOffset 
	 * @param GoogleSafeSearch $strSafeSearch 
	 * @return GoogleSearcher
	 * @author Jesse Bunch
	 */
	static function DoSearch($strAPIKey, $strSearchEngineID, $strQuery, $intLimit = 10, $intOffset = 1, $strSafeSearch = GoogleSafeSearch::OFF) {

		$refClass = __CLASS__;
		$objSearcher = new $refClass();

		// -------------------------------------
		//  Set IVARS
		// -------------------------------------
		
		$objSearcher->strAPIKey = $strAPIKey;
		$objSearcher->strSearchEngineID = $strSearchEngineID;
		$objSearcher->strQuery = $strQuery;
		$objSearcher->intLimit = ($intLimit > 8) ? 'filtered_cse' : $intLimit;
		$objSearcher->intOffset = (empty($intOffset)) ? 0 : $intOffset;

		switch($strSafeSearch) {

			case GoogleSafeSearch::HIGH:
			$objSearcher->strSafeSearch = GoogleSafeSearch::HIGH;
			break;

			case GoogleSafeSearch::MEDIUM:
			$objSearcher->strSafeSearch = GoogleSafeSearch::MEDIUM;
			break;

			case GoogleSafeSearch::OFF:
			default:
			$objSearcher->strSafeSearch = GoogleSafeSearch::OFF;
			break;

		}

		// -------------------------------------
		//  Validation
		// -------------------------------------

		if (!$objSearcher->_IsSearchValid()) {
			return $objSearcher;
		}

		// -------------------------------------
		//  Build params array
		// -------------------------------------
		
		$arrParams = array(
			'v' => '1.0',
			'userip' => $_SERVER['REMOTE_ADDR'],
			'key' => $objSearcher->strAPIKey,
			'cx' => $objSearcher->strSearchEngineID,
			'safe' => $objSearcher->strSafeSearch,
			'rsz' => $objSearcher->intLimit,
			'start' => $objSearcher->intOffset,
			'q' => $objSearcher->strQuery
		);
		
		// -------------------------------------
		//  Fire off the query
		// -------------------------------------

		$objSearcher->strResponse = $objSearcher->_FetchData($objSearcher::API_URL, $arrParams);

		if ($objSearcher->strResponse == FALSE) {
			$objSearcher->arrErrors[] = 'An error occurred while trying to query Google.';
			return $objSearcher;
		}
		
		// -------------------------------------
		//  Process the results
		// -------------------------------------
		
		$objSearcher->arrResults = $objSearcher->_ProcessResults($objSearcher->strResponse);
		
		if ($objSearcher->intStatusCode == '400') {
			$objSearcher->arrErrors[] = $objSearcher->strResponseDetails;
			return $objSearcher;
		}
		
		// -------------------------------------
		//  Finished!
		// -------------------------------------
		
		return $objSearcher;

	}

}

/**
* Enumerations for the Google Safe Search parameter
*
* @final
* @package		ExpressionEngine
* @subpackage	Addons
* @category	Module
* @author		Jesse Bunch
* @link		http://getbunch.com/
*/
final class GoogleSafeSearch {
	const HIGH = 'high';
	const MEDIUM = 'medium';
	const OFF = 'off';
}