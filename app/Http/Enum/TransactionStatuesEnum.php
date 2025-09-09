<?php

namespace App\Http\Enum;

enum TransactionStatuesEnum : string
{
    case pending = 'pending';
    case completed = 'completed';
    case cancelled = 'cancelled';

    case failed = 'failed';

}
