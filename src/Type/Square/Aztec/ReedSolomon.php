<?php

namespace Com\Tecnick\Barcode\Type\Square\Aztec;

use Com\Tecnick\Barcode\Exception as BarcodeException;

class ReedSolomon
{
    protected $size;
    protected $expTable;
    protected $logTable;
    # galois field
    protected $gfTable = [
            4 => 19,
            6 => 67,
            8 => 301,
            10 => 1033,
            12 => 4201
        ];

    public function __construct($wordSize)
    {
        $this->size = pow(2, $wordSize);
        $this->initialize($this->gfTable[$wordSize]);
    }

    # generate logarithm and anti-log tables
    protected function initialize($primitive)
    {
        $this->expTable = array_fill(0, $this->size, 0);
        $this->logTable = $this->expTable;

        $tab = 1;
        for ($add = 0; $add < $this->size; $add++) {
            $this->expTable[$add] = $tab;
            $tab <<= 1;
            if ($tab >= $this->size) {
                $tab ^= $primitive;
                $tab &= ($this->size - 1);
            }
        }
        for ($add = 0; $add < $this->size; $add++) {
            $this->logTable[$this->expTable[$add]] = $add;
        }
    }

    protected function fieldmultiply($abc, $bcd)
    {
        if ($abc == 0 || $bcd == 0) {
            return 0;
        }

        return $this->expTable[($this->logTable[$abc] + $this->logTable[$bcd]) % ($this->size - 1)];
    }

    protected function getPoly($coefficients)
    {
        while (!empty($coefficients) && $coefficients[0] == 0) {
            array_shift($coefficients);
        }

        return $coefficients;
    }

    protected function buildGenerator($ecBytes)
    {
        $lastGenerator = [1];

        for ($d = 1; $d <= $ecBytes; $d++) {
            $lastGenerator = $this->multiply([1, $this->expTable[$d]], $lastGenerator);
        }

        return $lastGenerator;
    }

    protected function multiply($bCoefficients, $aCoefficients)
    {
        # Coefficients are prepended 1 so can't be 0
        $aLength = count($aCoefficients);
        $bLength = count($bCoefficients);
        $product = array_fill(0, ($aLength + $bLength - 1), 0);

        for ($i = 0; $i < $aLength; $i++) {
            for ($j = 0; $j < $bLength; $j++) {
                $product[$i + $j] ^= ($this->fieldmultiply($aCoefficients[$i], $bCoefficients[$j]));
            }
        }

        if ($this->isZero($product)) {
                        throw new BarcodeException('Divide by 0');
        }

        return $this->getPoly($product);
    }

    protected function isZero($coefficients)
    {
        return $coefficients[0] == 0;
    }

    protected function addOrSubtract($largerCoefficients, $smallerCoefficients)
    {
        if ($this->isZero($smallerCoefficients)) {
            return $largerCoefficients;
        }
        if ($this->isZero($largerCoefficients)) {
            return $smallerCoefficients;
        }

        if (count($smallerCoefficients) > count($largerCoefficients)) {
            list($smallerCoefficients, $largerCoefficients) = [$largerCoefficients, $smallerCoefficients];
        }

        $lengthDiff = count($largerCoefficients) - count($smallerCoefficients);
        $sumDiff = array_slice($largerCoefficients, 0, $lengthDiff);
        $cont = count($largerCoefficients);
        for ($add = $lengthDiff; $add < $cont; $add++) {
            $sumDiff[$add] = $smallerCoefficients[$add - $lengthDiff] ^ $largerCoefficients[$add];
        }

        return $this->getPoly($sumDiff);
    }

    protected function multiplyByMonomial($degree, $coefficient, $coefficients)
    {
        if ($coefficient == 0) {
            return [0];
        }

        $cont = count($coefficients);
        $product = array_fill(0, ($cont + $degree), 0);

        for ($add = 0; $add < $cont; $add++) {
            $product[$add] = $this->fieldmultiply($coefficients[$add], $coefficient);
        }

        return $this->getPoly($product);
    }

    protected function divide($ecBytes, $data)
    {
        $otherDegree = $ecBytes + 1;
        $otherCoefficient = $this->buildGenerator($ecBytes);

        $one = $this->multiplyByMonomial($ecBytes, 1, $data);
        
        while (count($one) >= $otherDegree && !$this->isZero($one)) {
            $degreeDifference = count($one) - $otherDegree;
            $scale = $this->fieldmultiply($one[0], 1);
            $largerCoefficients = $this->multiplyByMonomial($degreeDifference, $scale, $otherCoefficient);

            $one = $this->addOrSubtract($largerCoefficients, $one);
        }

        return $one;
    }

    public function encodePadded(array $paddedData, $ecBytes)
    {
        array_splice($paddedData, -$ecBytes);

        $coefficients = $this->divide($ecBytes, $paddedData);
        $paddedCoefficients = array_pad($coefficients, -$ecBytes, 0);

        return array_merge($paddedData, $paddedCoefficients);
    }
}
