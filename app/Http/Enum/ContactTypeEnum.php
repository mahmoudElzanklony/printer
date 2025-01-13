<?php

namespace App\Http\Enum;

enum ContactTypeEnum : string
{
    case pending = 'pending';
    case review = 'review';

    case completed = 'completed';

}
