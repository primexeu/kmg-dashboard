<?php
// app/Models/IntakeLog.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class IntakeLog extends Model
{
    protected $fillable = ['idempotency_key','source','type','body','status','error','metadata'];
    protected $casts = [
        'body' => 'array',
        'metadata' => 'array'
    ];
}