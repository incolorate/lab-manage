<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = ['name', 'description'];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot("concentration");
    }


    // App\Models\Recipe.php
public function calculatePriceFor100g()
{
    $totalPrice = 0;

    foreach ($this->ingredients as $ingredient) {
        // Convert percentage to decimal (e.g., 10% becomes 0.1)
        $concentrationDecimal = $ingredient->pivot->concentration / 100;
        
        // Calculate price contribution: 
        // ingredient_price_per_kg * concentration * 0.1 (for 100g)
        $priceContribution = $ingredient->price * $concentrationDecimal * 0.1;
        
        $totalPrice += $priceContribution;
    }
    
    return round($totalPrice, 2);
}
}
