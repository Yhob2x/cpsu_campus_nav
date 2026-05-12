<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;
    
    protected $table = 'offices';
    
    protected $fillable = [
        'office_id',
        'name',
        'description',
        'building',
        'floor',
        'room_number',
        'lat',
        'lng',
        'category',
        'is_active',
        'working_hours',
        'contact_number',
        'email',
        'icon'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];
    
    // Helper method to get working hours formatted
    public function getWorkingHoursFormatted()
    {
        return $this->working_hours ?: '8:00 AM - 5:00 PM (Monday to Friday)';
    }
    
    // Scope for active offices
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Get distance from user coordinates
    public function getDistanceFrom($lat, $lng)
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat - $this->lat);
        $dLng = deg2rad($lng - $this->lng);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($this->lat)) * cos(deg2rad($lat)) * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}