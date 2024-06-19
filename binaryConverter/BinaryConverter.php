<?php

class BinaryConverter
{
    function decimalToBinary($decimal)
    {
        $result = [];

        while ($decimal > 0) {

            if ($decimal % 2 === 1) {
                $decimal = (int) ($decimal / 2);
                array_unshift($result, (int) 1);
            } else {
                $decimal = (int) ($decimal / 2);
                array_unshift($result, (int) 0);
            }
        }

        print_r(implode($result));
    }
}


