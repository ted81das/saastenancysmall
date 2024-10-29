<?php

namespace App\Models;

use Mpociot\Versionable\Version;

class TransactionVersion extends Version
{
    public $table = 'transaction_versions';
}
