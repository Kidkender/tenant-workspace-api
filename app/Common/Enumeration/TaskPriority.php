<?php

namespace App\Common\Enumeration;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
}
