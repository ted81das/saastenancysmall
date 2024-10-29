<?php

namespace App\Constants;

enum InvoiceStatus: string
{
    case UNRENEDERED = 'unrendered';
    case RENDERED = 'rendered';
}
