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
			$options = [
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			];
			$pdo = new PDO($dsn, 'root', '1', $options);

			//$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_pdo = $pdo;
		} catch (\Exception $e) {
			echo 'Could not connect to the database.';
		}
	}

	/**
	 * @param $phoneNumber
	 * @return array
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
				SELECT gcode_id AS 'id',
					   gcode_cns AS 'cns',
					   gcode_area_len AS 'area',
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

				while ($i <= $phoneLength) {
					foreach ($results as $result) {
						// $phoneLength += $result['area'];
						if (
							$result['cns'] == $pattern &&
							$result['min'] <= ($phoneLength - $patternLength)
							&& ($phoneLength - $patternLength) <= $result['max']
						) {
							$variants[] = [
								'id' => $result['id'],
								'phoneNumber' => [
									'cns' => $result['cns'],
									'number' => substr($phoneNumberString, $patternLength - 1)
								]
							];
						}
					}

					$pattern .= $phoneNumber[++$i];
					$patternLength = strlen($pattern);
				}

			}

			return $variants;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}

	}
}