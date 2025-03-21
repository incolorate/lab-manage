<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model {
    protected $fillable = [
        'name',
        'inci',
        'description',
        'moq',
        'price',
        'is_sample',
        'in_stock',
        'stock_amount',
        'supplier_id'
    ];
    
    protected $casts = [
        'is_sample' => 'boolean',
        'in_stock' => 'boolean',
        'moq' => 'decimal:2',
        'price' => 'decimal:2',
        'stock_amount' => 'decimal:2',
    ];

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