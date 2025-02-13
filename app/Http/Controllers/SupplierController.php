<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;



class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     *
     * @return Response
     */
    public function index(): Response
    {
        $suppliers = Supplier::with('ingredients')->get();
        
        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created supplier.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20'
        ]);

        $supplier = Supplier::create($validated);

        // Get the return path from the request, default to suppliers.index
        $returnTo = $request->input('return_to', 'suppliers.index');

        return to_route($returnTo);
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param Supplier $supplier
     * @return Response
     */
    public function edit(Supplier $supplier): Response
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier->load('ingredients')
        ]);
    }

    /**
     * Update the specified supplier.
     *
     * @param Request $request
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:20'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully');
    }

    /**
     * Remove the specified supplier.
     *
     * @param Supplier $supplier
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully');
    }
}