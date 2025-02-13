<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = ['name', 'contact_person', 'phone_number']; 

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
