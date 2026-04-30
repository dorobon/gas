<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class GasStation extends Model
{
    use HasFactory;

    protected $table = 'stations';

    protected $fillable = [
        'station_code',
        'province',
        'municipality',
        'locality',
        'postal_code',
        'address',
        'margin',
        'longitude',
        'latitude',
        'brand',
        'sale_type',
        'provider',
        'hours',
        'service_type',
        'municipality_code',
        'province_code',
        'autonomous_community_code',
    ];

    protected function casts(): array
    {
        return [
            'longitude' => 'float',
            'latitude' => 'float',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class, 'station_id');
    }

    public function latestPrice(): HasOne
    {
        return $this->hasOne(Price::class, 'station_id')->latestOfMany('collected_at');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim(($this->brand ?: 'Estación de servicio').' · '.$this->municipality);
    }

    public function getSaleTypeLabelAttribute(): string
    {
        return match (strtoupper((string) $this->sale_type)) {
            'P' => 'Público',
            'R' => 'Restringido',
            default => filled($this->sale_type) ? (string) $this->sale_type : 'No informado',
        };
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match (strtoupper((string) $this->service_type)) {
            'P' => 'Con personal',
            'A' => 'Autoservicio',
            'D' => 'Desatendido',
            default => filled($this->service_type) ? (string) $this->service_type : 'No informado',
        };
    }

    public function getMarginLabelAttribute(): string
    {
        return match (strtoupper((string) $this->margin)) {
            'I' => 'Izquierdo',
            'D' => 'Derecho',
            default => filled($this->margin) ? (string) $this->margin : 'No informado',
        };
    }

    public function getRouteSlugAttribute(): string
    {
        $parts = array_filter([
            $this->brand,
            $this->address,
            $this->municipality,
            $this->province,
            $this->postal_code,
        ]);

        return Str::slug(implode(' ', $parts));
    }

    public function getOpenStreetMapUrlAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=17/%s/%s',
            rawurlencode((string) $this->latitude),
            rawurlencode((string) $this->longitude),
            rawurlencode((string) $this->latitude),
            rawurlencode((string) $this->longitude),
        );
    }

    public function getOpenStreetMapEmbedUrlAttribute(): ?string
    {
        if ($this->latitude === null || $this->longitude === null) {
            return null;
        }

        $margin = 0.0045;
        $south = $this->latitude - $margin;
        $north = $this->latitude + $margin;
        $west = $this->longitude - $margin;
        $east = $this->longitude + $margin;

        return sprintf(
            'https://www.openstreetmap.org/export/embed.html?bbox=%s%%2C%s%%2C%s%%2C%s&layer=mapnik&marker=%s%%2C%s',
            rawurlencode((string) $west),
            rawurlencode((string) $south),
            rawurlencode((string) $east),
            rawurlencode((string) $north),
            rawurlencode((string) $this->latitude),
            rawurlencode((string) $this->longitude),
        );
    }
}
