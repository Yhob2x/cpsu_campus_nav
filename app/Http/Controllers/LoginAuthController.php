<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginAuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Attempt login
        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];
        
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Redirect based on role
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            
            return redirect()->intended('/dashboard');
        }
        
        return back()->withErrors([
            'error' => 'Invalid username or password.',
        ])->onlyInput('username');
    }
    
    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
    
    /**
     * User dashboard (for non-admin users)
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login');
        }
        
        // Check if user is admin, redirect to admin dashboard
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        }
        
        return view('user.dashboard', compact('user'));
    }
}