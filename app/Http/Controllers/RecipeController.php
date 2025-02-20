<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes with ingredient management options.
     */
    public function index()
    {
        // Load recipes with their ingredients for the datatable
        $recipes = Recipe::with(['ingredients' => function($query) {
            $query->with('supplier:id,name');
        }])->get();
        
        // Get all ingredients for the ingredient selector
        $allIngredients = Ingredient::with('supplier:id,name')
            ->select('id', 'name', 'description', 'supplier_id')
            ->get();
            
        // Get suppliers for the quick add dropdown
        $suppliers = Supplier::select('id', 'name')->get();
        
        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'allIngredients' => $allIngredients,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.pivot.concentration' => 'required|numeric|min:0|max:100'
        ]);
        
        $recipe = Recipe::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        
        // Prepare ingredients data for sync
        $ingredientsData = [];
        foreach ($validated['ingredients'] as $ingredient) {
            $ingredientsData[$ingredient['id']] = [
                'concentration' => $ingredient['pivot']['concentration']
            ];
        }
        
        $recipe->ingredients()->sync($ingredientsData);
        
        return redirect()->route('recipes.index');
    }

    /**
     * Update the specified recipe in storage.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.id' => 'required|exists:ingredients,id',
            'ingredients.*.pivot.concentration' => 'required|numeric|min:0|max:100'
        ]);
        
        $recipe->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);
        
        // Prepare ingredients data for sync
        $ingredientsData = [];
        foreach ($validated['ingredients'] as $ingredient) {
            $ingredientsData[$ingredient['id']] = [
                'concentration' => $ingredient['pivot']['concentration']
            ];
        }
        
        $recipe->ingredients()->sync($ingredientsData);
        
        return redirect()->route('recipes.index');
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();
        
        return redirect()->route('recipes.index');
    }
}