<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoDet extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'so_det';

    protected $fillable = [];

    public function rfts()
    {
        return $this->hasMany(Rft::class, 'so_det_id', 'id');
    }

    public function defects()
    {
        return $this->hasMany(Defect::class, 'so_det_id', 'id');
    }

    public function rejects()
    {
        return $this->hasMany(Reject::class, 'so_det_id', 'id');
    }

    public function reworks()
    {
        return $this->hasMany(Rework::class, 'so_det_id', 'id');
    }

    public function so()
    {
        return $this->belongsTo(So::class, 'id_so', 'id');
    }
}
