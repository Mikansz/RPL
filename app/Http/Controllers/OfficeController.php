<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::orderBy('name')->paginate(20);
        return view('offices.index', compact('offices'));
    }

    public function create()
    {
        return view('offices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:offices,name',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        Office::create([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->route('offices.index')
                        ->with('success', 'Kantor berhasil dibuat.');
    }

    public function show($id)
    {
        $office = Office::with(['schedules.user', 'attendances.user'])->findOrFail($id);
        return view('offices.show', compact('office'));
    }

    public function edit($id)
    {
        $office = Office::findOrFail($id);
        return view('offices.edit', compact('office'));
    }

    public function update(Request $request, $id)
    {
        $office = Office::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:offices,name,' . $id,
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $office->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
            'is_active' => $request->has('is_active') ? $request->is_active : $office->is_active,
        ]);

        return redirect()->route('offices.index')
                        ->with('success', 'Kantor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $office = Office::findOrFail($id);
        
        // Check if office has active schedules
        if ($office->schedules()->where('status', 'approved')->exists()) {
            return redirect()->back()
                            ->with('error', 'Cannot delete office with active schedules.');
        }

        $office->delete();

        return redirect()->route('offices.index')
                        ->with('success', 'Office deleted successfully.');
    }
}
