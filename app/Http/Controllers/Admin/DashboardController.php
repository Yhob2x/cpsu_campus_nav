<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller  // ← Changed class name
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
            'total_buildings' => Office::distinct('building')->count('building'),
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_staff' => User::where('role', 'staff')->count(),
            'active_offices' => Office::where('is_active', true)->count(),
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
        $processes = Process::with('steps')->get();
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
        $request->validate([
            'office_id' => 'required|unique:offices',
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'category' => 'required',
        ]);
        
        Office::create($request->all());
        
        return redirect()->route('admin.offices')->with('success', 'Office added successfully!');
    }
    
    /**
     * Update office
     */
    public function updateOffice(Request $request, $id)
    {
        $office = Office::where('office_id', $id)->firstOrFail();
        $office->update($request->all());
        
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