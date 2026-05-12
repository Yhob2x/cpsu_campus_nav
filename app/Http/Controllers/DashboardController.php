<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        // Statistics from database
        $stats = [
            'total_offices' => Office::count(),
            'total_processes' => Process::count(),
            'total_buildings' => 0,
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_staff' => User::where('role', 'staff')->count(),
            'active_offices' => Office::count(),
        ];
        
        // Recent offices (last 5)
        $recentOffices = Office::latest()->take(5)->get();
        
        // Chart data for navigation analytics
        $chartLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $chartData = [120, 145, 168, 190, 210, 185, 130];
        
        return view('admin.dashboard', compact('stats', 'recentOffices', 'chartLabels', 'chartData'));
    }
    
    /**
     * Show office management page
     */
    public function offices()
    {
        $offices = Office::all();
        return view('admin.offices', compact('offices'));
    }
    
    /**
     * Show process management page
     */
    public function processes()
    {
        $processes = Process::all();
        $offices = Office::all();
        return view('admin.processes', compact('processes', 'offices'));
    }
    
    /**
     * Show map editor page
     */
    public function mapEditor()
    {
        $offices = Office::all();
        return view('admin.map-editor', compact('offices'));
    }
    
    /**
     * Store new office
     */
    public function storeOffice(Request $request)
    {
        $validated = $request->validate([
            'office_id' => 'required|unique:offices',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'building' => 'required|string|max:255',
            'floor' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'category' => 'required|string',
            'working_hours' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
            'is_active' => 'boolean'
        ]);
        
        Office::create($validated);
        
        return redirect('/admin/offices')->with('success', 'Office added successfully!');
    }
    
    /**
     * Update office
     */
    public function updateOffice(Request $request, $id)
    {
        $office = Office::where('office_id', $id)->firstOrFail();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'building' => 'required|string|max:255',
            'floor' => 'nullable|string|max:100',
            'room_number' => 'nullable|string|max:50',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'category' => 'required|string',
            'working_hours' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'email' => 'nullable|email',
            'is_active' => 'boolean'
        ]);
        
        $office->update($validated);
        
        return redirect()->route('admin.offices')->with('success', 'Office updated successfully!');
    }
    
    /**
     * Delete office
     */
    public function destroyOffice($id)
    {
        $office = Office::where('office_id', $id)->firstOrFail();
        $office->delete();
        
        return redirect()->route('admin.offices')->with('success', 'Office deleted successfully!');
    }
}