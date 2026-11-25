<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\SafeDecimalCast;

class StarlaReference extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'starla_reference';
    protected $primaryKey = 'starla_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'table_ref',
        'input_value',
        'day_equivalent',
        'validated_by',
        'validated_on',
    ];

    protected $casts = [
        'day_equivalent' => 'string',
        'validated_on' => 'date',
    ];

    /**
     * The admin user who calibrated / validated this conversion record.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by', 'user_id');
    }
}
