<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Office;
use App\Models\Footwalk;

// Public API routes
Route::get('/offices', function () {
    return response()->json([
        'success' => true,
        'data' => Office::all()
    ]);
});

Route::get('/office/{id}', function ($id) {
    $office = Office::where('office_id', $id)->first();
    return response()->json([
        'success' => true,
        'data' => $office
    ]);
});

// Office CRUD
Route::post('/offices', function (Request $request) {
    try {
        if ($request->office_id) {
            $office = Office::where('office_id', $request->office_id)->first();
            $office->update($request->all());
        } else {
            $office = Office::create([
                'office_id' => 'OFF-' . strtoupper(uniqid()),
                'name' => $request->name,
                'building' => $request->building,
                'room_number' => $request->room_number,
                'category' => $request->category,
                'working_hours' => $request->working_hours,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'is_active' => true
            ]);
        }
        
        return response()->json(['success' => true, 'data' => $office]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Route::delete('/offices/{id}', function ($id) {
    $office = Office::where('office_id', $id)->first();
    if ($office) {
        $office->delete();
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Office not found'], 404);
});

// Footwalk API (clean version - no duplicates)
Route::get('/footwalks', function () {
    $footwalks = Footwalk::all();
    return response()->json(['success' => true, 'data' => $footwalks]);
});

Route::get('/footwalks/{id}', function ($id) {
    $footwalk = Footwalk::find($id);
    if ($footwalk) {
        return response()->json(['success' => true, 'data' => $footwalk]);
    }
    return response()->json(['success' => false, 'message' => 'Footwalk not found'], 404);
});

Route::post('/footwalks', function (Request $request) {
    try {
        // Check if this is an update (has id) or create (no id)
        if ($request->has('id') && $request->id) {
            // Update existing footwalk
            $footwalk = Footwalk::find($request->id);
            if ($footwalk) {
                $footwalk->update([
                    'name' => $request->name,
                    'type' => $request->type,
                    'color' => $request->color,
                    'width' => $request->width,
                    'coordinates' => $request->coordinates,
                    'description' => $request->description
                ]);
                return response()->json(['success' => true, 'data' => $footwalk]);
            } else {
                return response()->json(['success' => false, 'message' => 'Footwalk not found'], 404);
            }
        } else {
            // Create new footwalk
            $footwalk = Footwalk::create([
                'name' => $request->name,
                'type' => $request->type,
                'color' => $request->color,
                'width' => $request->width,
                'coordinates' => $request->coordinates,
                'description' => $request->description
            ]);
            return response()->json(['success' => true, 'data' => $footwalk]);
        }
    } catch (Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Route::delete('/footwalks/{id}', function ($id) {
    $footwalk = Footwalk::find($id);
    if ($footwalk) {
        $footwalk->delete();
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Footwalk not found'], 404);
});