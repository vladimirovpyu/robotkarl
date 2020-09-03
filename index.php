<?php

class Parser
{
    // ����������� ����� �������
    const NUMBER_SIZE = 10;
    // ��� ������
    const COUNTRY_CODE = '+7';
    // ��� ��������
    const PHONE_CODE = '9';

    /**
     * ������� 9 � ��� ���� ����� ��� �� ������ �������� �����
     * @param $number
     * @return string
     */
    static private function trim($number)
    {
        return ltrim(substr($number,1),'0');
    }

    /**
     * format parsed range
     * @param $number - ����� ��� ��������������
     * @param $size - dimension (����������� ��� ������� � ������, ��� ����� ����� ���������� �� ������ ������)
     * @param $incSize - ����������� ����������� �����, ���� ��� ������ ��� ���������� �����, ����� ������ ������
     */
    static private function format($number, $size, $incSize)
    {
        // ��� ����� ������ �������� �����
        $number = rtrim($number,'0');
        if ( $number == '') $number = '0';

        // ���������� ������ ����������� ����������, ���� �� ������ ����������� �������, �� �������� ������
        $numSize = (self::NUMBER_SIZE - $size - 1) + strlen($number);
        $zeroCount = self::NUMBER_SIZE - $numSize - $incSize;
        //if ($zeroCount < 0) $zeroCount = 0;

        // ��������� ���������
        return self::COUNTRY_CODE . self:: PHONE_CODE . str_repeat('0', self::NUMBER_SIZE - $size - 1) . $number . str_repeat('0', $zeroCount);
    }

    /**
     * ���������� ������� ��������� �������� ����� � �����
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
     * ������ ��������
     * @param $start
     * @param $end
     * @return []
     */
    static public function parseRange($start, $end)
    {
        // ����������� �� �������, ��������� � �����
        $start = (int)self::trim($start);
        $end = (int)self::trim($end);

        // $endSize - ��� ������ ����������� �������(����������), �� ��������� �� $start ��� ������ ����������, �.�. $start ����� ���� 0000,
        // ���������� �� $end;
        $endSize = strlen($end);

        // $end +1 ����� �� ����� ���� �������� � 9999 �� �����.
        $end = $end + 1;

        // ������ ����������� ��������� �������
        // ����� ������� � ������� ������, ���� ���������� ��������, �� ��� ���� ������� ����������.
        $ranges = function($start, $end) use (&$ranges, $endSize)
        {
            $result = [];
            // ������� ������� ����� $end � $start
            $diff = $end - $start;
            // ���������� �����������
            $diffSize = strlen($diff); // ��� 62000 ������ 5 - ��� 4 ����

            // ���� ��������� �������� ����� � $start, � � $diff ������ �������� � ����� �� ��� �����������.
            // �� ��� � ����� ����������� ���� $start �����
            if ($start == 0) {
                $lastNumSize = $diffSize;
            } else {
                $lastNumSize = min (self::getLastNumPosition($start), $diffSize);
            }

            // ������� ����������
            $inc = pow(10, $lastNumSize-1);

            $result[] = self::format($start, $endSize, strlen($inc));

            // ������ ���������� $start �� 1 �����, �� ����� $lastNumSize
            $start += $inc;

            if ($start == $end) return $result;
            else return array_merge($result, $ranges($start, $end));

        };
        return $ranges($start,$end);
    }
}

$data ="������� 9000000000 9000061999
������ 9000062000 9000062999
��� 9000063000 9000099999
������� 9000100000 9000199999
������ 9000200000 9000299999
��� 9000300000 9000499999
������� 9000500000 9000599999
������ 9000600000 9000999999
��� 9001000000 9001099999
������� 9001100000 9001199999
������ 9001200000 9001399999
��� 9001400000 9001899999
������� 9001900000 9001909999
������ 9001910000 9001919999
��� 9001920000 9001929999
������� 9001930000 9001939999
������ 9001940000 9001949999
��� 9001950000 9001959999
������� 9001960000 9001969999
������ 9001970000 9002169999
��� 9002170000 9002187999";

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


