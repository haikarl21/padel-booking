<?php

namespace App\Http\Controllers;

use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCourtController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courts = Court::orderBy('created_at','desc')->paginate(10);
        $total = Court::count();
        // count active records on this page
        $active = $courts->where('status','active')->count();
        return view('admin.courts.index', compact('courts','total','active'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.courts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_hour' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        // generate slug and ensure uniqueness
        $baseSlug = Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Court::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        $data['slug'] = $slug;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('courts','public');
        }

        $data['status'] = $data['status'] ?? 'inactive';

        Court::create($data);

        return redirect()->route('courts.index')->with('success','Court created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $court = Court::findOrFail($id);
        return view('admin.courts.edit', compact('court'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $court = Court::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price_per_hour' => 'required|numeric',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        // regenerate slug if name changed
        if (isset($data['name']) && $data['name'] !== $court->name) {
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Court::where('slug', $slug)->where('id', '!=', $court->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('image')) {
            // delete old
            if ($court->image_path) {
                Storage::disk('public')->delete($court->image_path);
            }
            $data['image_path'] = $request->file('image')->store('courts','public');
        }

        $data['status'] = $data['status'] ?? 'inactive';

        $court->update($data);

        return redirect()->route('courts.index')->with('success','Court updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $court = Court::findOrFail($id);
        if ($court->image_path) {
            Storage::disk('public')->delete($court->image_path);
        }
        $court->delete();
        return redirect()->route('courts.index')->with('success','Court deleted.');
    }
}
