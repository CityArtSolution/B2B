<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        return view('admin.branch.index', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
        $name = [
            'ar' => $request->name_ar,
            'en' => $request->name_en,
            'ur' => $request->name_ur,
            'in' => $request->name_in,
        ];
        
        $branch = Branch::create([
            'name' => $name,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

    return redirect()->back()->with('success', __('Branch created successfully'));
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);

        return response()->json($branch);
    }

    public function update(Request $request)
    {
        $id = $request->id ;
        $branch = Branch::findOrFail($id);
        $name = [
            'ar' => $request->name_ar,
            'en' => $request->name_en,
            'ur' => $request->name_ur,
            'in' => $request->name_in,
        ];
        $branch->update([
            'name' => $name,
            'address' => $request->address,
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

    return redirect()->back()->with('success', __('Updated successfully'));
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(['status' => 'deleted']);
    }
}
