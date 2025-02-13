<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{

    protected $fillable = ['name', 'description', 'supplier_id'];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class)
            ->withPivot('concentration');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
