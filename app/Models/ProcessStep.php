<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessStep extends Model
{
    use HasFactory;
    
    protected $table = 'process_steps';
    
    protected $fillable = [
        'process_id',
        'step_number',
        'title',
        'description',
        'duration',
        'responsible_person'
    ];
    
    public function process()
    {
        return $this->belongsTo(Process::class);
    }
}