<?php

namespace App\Service;

final class OtpGenerator
{
    public function __construct(
        private readonly int $length = 6
    ) {}

    public function generateNumeric(): string
    {
        $len = max(4, min(10, $this->length)); // простая защита от странных значений
        $min = 10 ** ($len - 1);
        $max = (10 ** $len) - 1;

        return (string) random_int($min, $max);
    }
}
