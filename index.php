<?php

class Parser
{
    // размерность наших номеров
    const NUMBER_SIZE = 10;
    // код города
    const COUNTRY_CODE = '+7';
    // код телефона
    const PHONE_CODE = '9';

    /**
     * убираем 9 и все ноли после нее до первой значащей цифры
     * @param $number
     * @return string
     */
    static private function trim($number)
    {
        return ltrim(substr($number,1),'0');
    }

    /**
     * format parsed range
     * @param $number - номер для форматирования
     * @param $size - dimension (размерность для номеров с нолями, без нолей можно определить по самому номеру)
     * @param $incSize - размерность добавляемой части, если она меньше чем получаемый номер, нужно добить нолями
     */
    static private function format($number, $size, $incSize)
    {
        // нам нужны только значащие цифры
        $number = rtrim($number,'0');
        if ( $number == '') $number = '0';

        // определяем размер полученного результата, если он меньше добавляемой разницы, то добиваем нолями
        $numSize = (self::NUMBER_SIZE - $size - 1) + strlen($number);
        $zeroCount = self::NUMBER_SIZE - $numSize - $incSize;
        //if ($zeroCount < 0) $zeroCount = 0;

        // формируем результат
        return self::COUNTRY_CODE . self:: PHONE_CODE . str_repeat('0', self::NUMBER_SIZE - $size - 1) . $number . str_repeat('0', $zeroCount);
    }

    /**
     * Определяем позицию последней значащей цифры в числе
     * @param $number
     */
    static private function getLastNumPosition($number)
    {
        $arr = array_reverse(str_split($number));
        $lastNumSize = 1;
        foreach ($arr as $num) {
            if ($num == 0) $lastNumSize++;
            else break;
        }
        return $lastNumSize;
    }

    /**
     * Парсит диапазон
     * @param $start
     * @param $end
     * @return []
     */
    static public function parseRange($start, $end)
    {
        // избавляемся от лишнего, переводим в целые
        $start = (int)self::trim($start);
        $end = (int)self::trim($end);

        // $endSize - это просто размерность номеров(диапазонов), но поскольку по $start его сложно определить, т.к. $start может быть 0000,
        // определяем по $end;
        $endSize = strlen($end);

        // $end +1 чтобы не нужно было возиться с 9999 на конце.
        $end = $end + 1;

        // делаем рекурсивную анонимную функцию
        // можно сделать и обычным циклом, либо нормальной функцией, но так тоже красиво получается.
        $ranges = function($start, $end) use (&$ranges, $endSize)
        {
            $result = [];
            // считаем разницу между $end и $start
            $diff = $end - $start;
            // определяем размерность
            $diffSize = strlen($diff); // для 62000 выдаст 5 - это 4 нуля

            // ищем последнюю значащую цифру в $start, а в $diff первую значащую и берез из них минимальную.
            // на нее и будем увеличивать наше $start число
            if ($start == 0) {
                $lastNumSize = $diffSize;
            } else {
                $lastNumSize = min (self::getLastNumPosition($start), $diffSize);
            }

            // сколько прибавляем
            $inc = pow(10, $lastNumSize-1);

            $result[] = self::format($start, $endSize, strlen($inc));

            // теперь прибавляем $start на 1 цифру, на месте $lastNumSize
            $start += $inc;

            if ($start == $end) return $result;
            else return array_merge($result, $ranges($start, $end));

        };
        return $ranges($start,$end);
    }
}

$data ="мегафон 9000000000 9000061999
билайн 9000062000 9000062999
мтс 9000063000 9000099999
мегафон 9000100000 9000199999
билайн 9000200000 9000299999
мтс 9000300000 9000499999
мегафон 9000500000 9000599999
билайн 9000600000 9000999999
мтс 9001000000 9001099999
мегафон 9001100000 9001199999
билайн 9001200000 9001399999
мтс 9001400000 9001899999
мегафон 9001900000 9001909999
билайн 9001910000 9001919999
мтс 9001920000 9001929999
мегафон 9001930000 9001939999
билайн 9001940000 9001949999
мтс 9001950000 9001959999
мегафон 9001960000 9001969999
билайн 9001970000 9002169999
мтс 9002170000 9002187999";

$data = explode("\n",$data);
$parsed = [];
foreach ($data as $row) {
    $row = explode(" ",$row);
    $parsed[] = ['range'=> "#{$row[1]} {$row[2]}", 'parsed' => Parser::parseRange($row[1], $row[2])];
}

foreach ($parsed as $row) {
    echo $row['range']."\n";
    foreach ($row['parsed'] as $parsed) {
        echo $parsed."\n";
    }
}


