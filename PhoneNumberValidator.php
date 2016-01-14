<?php
/**
 * @author e 13.01.2016
 */

class PhoneNumberValidator
{
	/**
	 * @var PDO
     */
	private $_pdo;

	/**
	 * PhoneNumberValidator constructor.
	 */
	public function __construct()
	{
		try {
			$dsn = "mysql:host=localhost;dbname=test";
			$options = array(
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			);
			$pdo = new PDO($dsn, 'root', '1', $options);

			//$db = new PDO('mysql:host=localhost;dbname=test;port=3306,root,1');
			//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//$db->exec("SET NAMES 'utf8'");
			$this->_pdo = $pdo;
		} catch (\Exception $e) {
			echo 'Could not connect to the database.';
		}
	}

	/**
	 * @param $phoneNumber
	 * @return string
	 */
	public function validate($phoneNumber)
	{
		try {
			// Выделим из ввода все цифры и проверим длину номера
			preg_match_all('#\d{1}#', $phoneNumber, $matches);
			if (count($matches[0]) < 9) {
				throw new \Exception('Phone number is too short (at least 9 digits)');
			}
			$phoneNumber = $matches[0];
			$phoneNumberString = implode($phoneNumber);

			$query = $this->_pdo->query("
				SELECT gcode_cns AS 'cns',
					   gcode_nr_min_len AS 'min',
					   gcode_nr_max_len AS 'max'
				FROM geo_codes
				WHERE gcode_cns LIKE '{$phoneNumber[0]}%';
			");

			$variants = [];
			if ($results = $query->fetchAll(PDO::FETCH_ASSOC)) {
				$i = 0;
				$pattern = $phoneNumber[$i];
				$phoneLength = count($phoneNumber);
				$patternLength = strlen($pattern);

				while (strlen($pattern) <= $phoneLength) {
					foreach ($results as $result) {
						if (
							$result['cns'] == $pattern &&
							$result['min'] <= ($phoneLength - 1 - $patternLength)
							&& ($phoneLength - 1 - $patternLength) <= $result['max']
						) {
							$variants[] = [
								$result['cns'],
								substr($phoneNumberString, $patternLength - 1)
							];
						}
					}

					$pattern .= $phoneNumber[++$i];
				}

			}

			return $variants;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}

	}
}