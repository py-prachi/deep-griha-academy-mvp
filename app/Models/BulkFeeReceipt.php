<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkFeeReceipt extends Model
{
    protected $fillable = [
        'session_id', 'fee_category', 'amount_received', 'received_date', 'remark', 'recorded_by',
    ];

    protected $casts = [
        'received_date' => 'date',
        'amount_received' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
