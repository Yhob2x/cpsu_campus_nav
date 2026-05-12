<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;
    
    protected $table = 'processes';
    
    protected $fillable = [
        'name',
        'description',
        'office_id',
        'estimated_time',
        'requirements',
        'steps'
    ];
    
    protected $casts = [
        'requirements' => 'array',
        'steps' => 'array',
    ];
    
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
    
    public function steps()
    {
        return $this->hasMany(ProcessStep::class);
    }
}