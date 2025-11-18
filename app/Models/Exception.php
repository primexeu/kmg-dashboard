<?php
// app/Models/Exception.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exception extends Model
{
    protected $fillable = [
        'order_id',
        'order_confirmation_id',
        'code',
        'severity',
        'status',
        'message',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
