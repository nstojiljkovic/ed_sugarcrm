<?php
namespace EssentialDots\EdSugarcrm\Persistence\Generic\Storage;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Nikola Stojiljkovic, Essential Dots d.o.o. Belgrade
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

class SugarCRMRESTHandle implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var string
	 */
	protected $rest_url;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $session;

	/**
	 * @var bool
	 */
	protected $logged_in;

	/**
	 * @var array
	 */
	protected $parametersOrder = array(
		'set_entry' => array('session', 'module_name', 'name_value_list'),
		'get_entries' => array('session', 'module_name', 'ids', 'select_fields', 'link_name_to_fields_array', 'track_view'),
		'get_entry' => array('session', 'module_name', 'id', 'select_fields', 'link_name_to_fields_array', 'track_view'),
		'get_entry_list' => array('session', 'module_name', 'query', 'order_by', 'offset', 'select_fields', 'link_name_to_fields_array', 'max_results', 'deleted', 'favorites'),
		'get_entries_count' => array('session', 'module_name', 'query', 'deleted'),
		'get_available_modules' => array('session', 'filter'),
		'get_module_fields' => array('session', 'module_name', 'fields')
	);

	/**
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @param string $rest_url
	 */
	public function setRESTUrl($rest_url) {
		$this->rest_url = $rest_url;
	}

	/**
	 * @return string
	 */
	public function getRESTUrl() {
		return $this->rest_url;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @param null $rest_url
	 * @param null $username
	 * @param null $password
	 * @param bool $md5_password
	 * @return bool
	 */
	public function connect($rest_url = null, $username = null, $password = null, $md5_password = true) {
		if (!is_null($rest_url)) {
			$this->rest_url = $rest_url;
		}

		if (!is_null($username)) {
			$this->username = $username;
		}

		if (!is_null($password)) {
			$this->password = $password;
		}

		if ($this->login($md5_password)) {
			$this->logged_in = TRUE;
			$data['session'] = $this->session;

			return true;
		} else {
			$this->logged_in = FALSE;

			return false;
		}
	}

	/**
	 * Function: login()
	 * Parameters:   none
	 * Description:  Makes a 'login' API call which authenticates based on the $username
	 *       and $password class variables. If the login call succeeds, sets
	 *       the $session class variable as the session ID.
	 * Returns:  Returns TRUE on success, otherwise FALSE
	 */
	protected function login($md5_password = true) {

		// run md5 on password if needed
		$password = $this->password;
		if ($md5_password) {
			$password = md5($this->password);
		}

		$result = $this->rest_request(
			'login',
			array(
				'user_auth' => array('user_name' => $this->username, 'password' => $password),
				'name_value_list' => array(array('name' => 'notifyonsave', 'value' => 'true'))
			)
		);
		if (isset($result['id'])) {
			$this->session = $result['id'];

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Function: rest_request()
	 * Parameters:   $call_name  = (string) the API call name
	 *       $call_arguments = (array) the arguments for the API call
	 * Description:  Makes an API call given a call name and arguments
	 *       checkout http://developers.sugarcrm.com/documentation.php for documentation
	 *       on the specific API calls
	 * Returns:  An array with the API call response data
	 */
	protected function rest_request($call_name, $call_arguments) {

		$ch = curl_init();

		$post_data = array(
			'method' => $call_name,
			'input_type' => 'JSON',
			'response_type' => 'JSON',
			'rest_data' => json_encode($call_arguments)
		);

		curl_setopt($ch, CURLOPT_URL, $this->rest_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$output = curl_exec($ch);
		curl_close($ch);

		$response_data = json_decode($output, true);

		$json_last_error = json_last_error();
		if ($json_last_error !== JSON_ERROR_NONE) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\RemoteAPIException('JSON decode error code: '.$json_last_error, 1242814377);
		}

		if ($response_data['name'] && $response_data['number'] && $response_data['description']) {
			throw new \EssentialDots\EdSugarcrm\Persistence\Generic\Storage\Exception\RemoteAPIException("Error number {$response_data['number']}: {$response_data['name']}. Description: {$response_data['description']}.", $response_data['number']);
		}

		return $response_data;
	}

	/**
	 * Function: is_valid_id($id)
	 * Parameters:   $id = (string) the SugarCRM record ID
	 * Description:  Checks to see if the given string is in the valid
	 *       format for a SugarCRM record ID. This is for input
	 *       data sanitation, does not actually check to see if
	 *       if there is a record with the given ID.
	 * Returns:  TRUE if valid format, otherwise FALSE
	 */
	public function is_valid_id($id) {
		if (!is_string($id)) return FALSE;
		return preg_match("/[0-9a-z\-]+/", $id);
	}

	/**
	 * @param array $apiParameters
	 * @param string $method
	 * @return mixed
	 */
	public function execQuery(array $apiParameters, $method = 'get_entry_list') {
		$apiParameters['session'] = $this->session;

		$sortedParameters = array();
		foreach ($this->parametersOrder[$method] as $parameterKey) {
			$sortedParameters[$parameterKey] = $apiParameters[$parameterKey];
		}

		$result = $this->rest_request(
			$method,
			$sortedParameters
		);

		return $result;
	}

	/**
	 * Function: is_logged_in()
	 * Parameters:   none
	 * Description:  Simple getter for logged_in private variable
	 * Returns:  boolean
	 */
	public function is_logged_in() {
		return $this->logged_in;
	}

	/**
	 * Function: __destruct()
	 * Parameters:   none
	 * Description:  Closes the API connection when the PHP class
	 *       object is destroyed
	 * Returns:  nothing
	 */
	public function __destruct() {
		if ($this->logged_in) {
			$this->rest_request(
				'logout',
				array(
					'session' => $this->session
				)
			);
		}
	}
}
