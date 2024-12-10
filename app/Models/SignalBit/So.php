<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class So extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'so';

    protected $fillable = [];

    public function soDet()
    {
        return $this->hasMany(SoDet::class, 'id_so', 'id');
    }

    public function actCosting()
    {
        return $this->belongsTo(ActCosting::class, 'id_cost', 'id');
    }
}
