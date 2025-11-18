<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'uploader_id',
        // Note: is_external and original_name are stored in description JSON
        // is_external can be determined by checking if file_path starts with http:// or https://
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

