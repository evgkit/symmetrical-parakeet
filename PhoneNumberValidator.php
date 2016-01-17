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
     * Минимальный размер телефонного номера с кодом страны и кодом оператора
     * @var int
     */
    private static $_min = 8;

    /**
     * PhoneNumberValidator constructor.
     */
    public function __construct()
    {

    }

    /**
     * Подключение к БД
     * @throws Exception
     */
    private function init()
    {
        try {
            $options = [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            ];
            $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '1', $options);

            $this->_pdo = $pdo;
        } catch (\Exception $e) {
            throw new \Exception('Ошибка: Невозможно подключиться к базе данных');
        }
    }

    /**
     * @param $input
     * @return array
     */
    public function run($input)
    {
        try {
            $this->init();

            $number = $this->filter($input);
            $geoCodes = $this->getGeoCodes($number[0]);
            $data = $this->validate($number, $geoCodes);

            $data = $this->format($data);

            return [
                'message' => 'Найдены следующие значения:',
                'data'    => $data
            ];
        } catch (\Exception $e) {
            return [
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Фильтрует ввод сохраняя только цифры и отдаёт их в виде массива
     * @param $input
     * @return mixed
     * @throws Exception
     */
    private function filter($input)
    {
        preg_match_all('#\d{1}#', $input, $matches);

        if (PhoneNumberValidator::$_min <= count($matches[0])) {
            return $matches[0];
        } else {
            throw new \Exception('Номер слишком короткий. Не менее 8 цифр +X-(X)-XXX-XXX');
        }
    }

    /**
     * Тут магия
     * @param $number
     * @param $geoCodes
     * @return array
     * @throws Exception
     */
    private function validate($number, $geoCodes)
    {
        $data = [];
        $currentDigit = 0;
        $mask = $number[$currentDigit];
        $numberLength = count($number);
        $maskLength = strlen($mask);

        while ($currentDigit <= $numberLength) {
            foreach ($geoCodes as $geoCode) {
                $min = $geoCode['gcode_nr_min_len'] + $geoCode['gcode_area_len'];
                $max = $geoCode['gcode_nr_max_len'] + $geoCode['gcode_area_len'];
                if (
                    $geoCode['gcode_cns'] == $mask &&
                    $min <= ($numberLength - $maskLength)
                    && ($numberLength - $maskLength) <= $max
                ) {
                    $data[] = array_merge($geoCode, [
                        'number' => substr(implode($number), $maskLength)
                    ]);
                }
            }

            $mask .= $number[++$currentDigit];
            $maskLength = strlen($mask);
        }

        if ($data) {
            return $data;
        } else {
            throw new \Exception('Нет совпадений');
        }
    }

    /**
     * Запрашивает все записи начинающиеся с первой цифры номера телефона, кэширует запрос, не экономит на спичках.
     * @param $firstDigit
     * @return array
     * @throws Exception
     */
    private function getGeoCodes($firstDigit)
    {
        // TODO: написать кэширование
        $query = $this->_pdo->query("
            SELECT *
            FROM geo_codes
            WHERE gcode_cns LIKE '{$firstDigit}%';
        ");

        if ($geoCodes = $query->fetchAll(PDO::FETCH_ASSOC)) {
            return $geoCodes;
        } else {
            throw new \Exception('Нет совпадений');
        }
    }

    /**
     * Форматируем вывод для фронтенда
     * @param $data
     * @return array
     */
    private function format($data)
    {
        $unnecessaryKeys = [
            'gcode_id',
            'gcode_co_id',
            'gcode_cns',
            'gcode_area_len',
            'gcode_nr_min_len',
            'gcode_nr_max_len',
        ];

        $labels = [
            'gcode_co_code'  => 'Код страны',
            'gcode_def_code' => 'Код оператора',
            'number'         => 'Номер телефона',
            'gcode_co_id3'   => 'Страна',
            'gcode_op_name'  => 'Наименование оператора',
            'gcode_op_id'    => 'Идентификатор оператора',
            'gcode_date'     => 'Дата',
        ];

        $labels = array_reverse($labels, true);

        foreach ($data as &$item) {
            foreach ($unnecessaryKeys as $key) {
                unset($item[$key]);
            }

            foreach ($labels as $code => $label) {
                $item[$code] = [
                    'name'  => $label,
                    'value' => $item[$code]
                ];

                $item = [$code => $item[$code]] + $item;
            }
        }

        return $data;
    }
}