<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Court;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CourtController extends Controller
{
    public function index()
    {
        $courts = Court::orderBy('created_at', 'desc')->get();
        return view('admin.courts.index', compact('courts'));
    }

    public function create()
    {
        return view('admin.courts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'price_per_hour' => 'required|numeric',
            'image_path' => 'nullable|image',
            'status' => 'required',
        ]);
        // Generate unique slug
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (\App\Models\Court::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $data['slug'] = $slug;
        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('courts', 'public');
        }
        Court::create($data);
        return redirect()->route('admin.courts.index')->with('success', 'Court created successfully.');
    }

    public function edit(Court $court)
    {
        return view('admin.courts.edit', compact('court'));
    }

    public function update(Request $request, Court $court)
    {
        $data = $request->validate([
            'name' => 'required',
            'price_per_hour' => 'required|numeric',
            'image_path' => 'nullable|image',
            'status' => 'required',
        ]);
        if ($request->hasFile('image_path')) {
            if ($court->image_path) Storage::disk('public')->delete($court->image_path);
            $data['image_path'] = $request->file('image_path')->store('courts', 'public');
        }
        $court->update($data);
        return redirect()->route('admin.courts.index')->with('success', 'Court updated successfully.');
    }

    public function destroy(Court $court)
    {
        if ($court->image_path) Storage::disk('public')->delete($court->image_path);
        $court->delete();
        return redirect()->route('admin.courts.index')->with('success', 'Court deleted successfully.');
    }
}
