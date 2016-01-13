<?php
/**
 * @author e 13.01.2016
 */

namespace e;

use PDO;

class PhoneNumberValidator
{
	private $_db;

	/**
	 * PhoneNumberValidator constructor.
	 */
	public function __construct()
	{
		try {
			$db = new PDO('mysql:host=localhost;dbname=test;port=3306,root,1');
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->exec("SET NAMES 'utf8'");
			$this->_db = $db;
		} catch (\Exception $e) {
			echo 'Could not connect to the database.';
			exit();
		}
	}

	public function validate()
	{

	}
}