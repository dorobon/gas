<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    use HasFactory;

    protected $table = 'prices';

    protected $fillable = [
        'station_id',
        'collected_at',
        'gas95_e5',
        'gas95_e10',
        'gas95_e25',
        'gas95_e5_premium',
        'gas95_e85',
        'gas98_e5',
        'gas98_e10',
        'renewable_gasoline',
        'diesel_a',
        'diesel_b',
        'diesel_premium',
        'renewable_diesel',
        'bioethanol',
        'pct_bio',
        'biodiesel',
        'pct_ester',
        'glp',
        'gnc',
        'gnl',
        'biogas_cng',
        'biogas_lng',
        'hydrogen',
        'adblue',
        'ammonia',
        'methanol',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'gas95_e5' => 'float',
            'gas95_e10' => 'float',
            'gas95_e25' => 'float',
            'gas95_e5_premium' => 'float',
            'gas95_e85' => 'float',
            'gas98_e5' => 'float',
            'gas98_e10' => 'float',
            'renewable_gasoline' => 'float',
            'diesel_a' => 'float',
            'diesel_b' => 'float',
            'diesel_premium' => 'float',
            'renewable_diesel' => 'float',
            'bioethanol' => 'float',
            'pct_bio' => 'float',
            'biodiesel' => 'float',
            'pct_ester' => 'float',
            'glp' => 'float',
            'gnc' => 'float',
            'gnl' => 'float',
            'biogas_cng' => 'float',
            'biogas_lng' => 'float',
            'hydrogen' => 'float',
            'adblue' => 'float',
            'ammonia' => 'float',
            'methanol' => 'float',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(GasStation::class, 'station_id');
    }
}
