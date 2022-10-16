<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportZipCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'il',
        'ilce',
        'semt',
        'mahalle',
        'pk',
    ];
}
