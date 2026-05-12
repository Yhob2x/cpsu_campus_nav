<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Footwalk extends Model
{
    use HasFactory;
    
    protected $table = 'footwalks';
    
    protected $fillable = [
        'name',
        'type',
        'color',
        'width',
        'coordinates',
        'description'
    ];
    
    protected $casts = [
        'coordinates' => 'array',
        'width' => 'decimal:2'
    ];
}