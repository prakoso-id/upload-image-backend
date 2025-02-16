<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'path',
        'label',
        'imageUrl'
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function($model) {
            $model->created_at = now();
            $model->updated_at = null;
        });

        static::updating(function($model) {
            $model->updated_at = now();
        });
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('Y-m-d H:i:s');
    }
}
