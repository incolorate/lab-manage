<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Supplier;

class IngredientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    
        return Inertia::render('Ingredients/Index', [
            'ingredients' => Ingredient::with('supplier')->get(),
            'suppliers' => Supplier::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inci' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'moq' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'is_sample' => 'boolean',
            'in_stock' => 'boolean',
            'stock_amount' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);
    
        Ingredient::create($validated);
    
        return to_route('ingredients.index');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'inci' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'moq' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'is_sample' => 'boolean',
            'in_stock' => 'boolean',
            'stock_amount' => 'nullable|numeric|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id'
        ]);

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($validated);

        return to_route('ingredients.index')->with('message', 'Ingredient updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();
    
        return to_route('ingredients.index')
            ->with('message', 'Ingredient deleted successfully');
    }
}
