<?php

namespace App\Enums;

enum UserAccountRole: int
{
    case Owner = 1;
    case User = 2;
}
