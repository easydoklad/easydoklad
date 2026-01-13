<?php

namespace App\Analytics;

enum Trend: string
{
    case Increasing = 'increasing';
    case None = 'none';
    case Decreasing = 'decreasing';
}
