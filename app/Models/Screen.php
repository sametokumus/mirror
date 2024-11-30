<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    protected $fillable = ['title', 'type', 'content', 'is_required', 'sequence', 'active'];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}
