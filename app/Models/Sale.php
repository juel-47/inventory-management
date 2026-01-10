<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'invoice_no',
        'outlet_user_id',
        'user_id',
        'date',
        'total_amount',
        'note',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outletUser()
    {
        return $this->belongsTo(User::class, 'outlet_user_id');
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
