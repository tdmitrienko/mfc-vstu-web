<?php

declare(strict_types=1);

namespace App\DTO;

enum ApplicantStatusEnum: string
{
    case Student = 'student';
    case Employee = 'employee';
}
