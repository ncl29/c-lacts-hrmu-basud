<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    use HasFactory;

    protected $table = 'offices';
    protected $primaryKey = 'office_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'office_name',
        'is_routing_point',
    ];

    protected $casts = [
        'is_routing_point' => 'boolean',
    ];

    /**
     * Employees assigned to this office.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'office_id', 'office_id');
    }

    /**
     * Routing scan logs recorded at this office station.
     */
    public function routingLogs(): HasMany
    {
        return $this->hasMany(RoutingLog::class, 'office_id', 'office_id');
    }
}
