<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    protected $table = 'quote_requests';

    protected $fillable = [
        'code',
        'contact_name',
        'phone',
        'event_type',
        'number',
        'note',
        'status',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    public static function generateCode(): string
    {
        $prefix = 'QTN';
        $last = static::orderByDesc('id')->first();
        $num = $last ? ((int) preg_replace('/^' . preg_quote($prefix, '/') . '/', '', $last->code) + 1) : 1;
        return $prefix . str_pad((string) $num, 2, '0', STR_PAD_LEFT);
    }
}
