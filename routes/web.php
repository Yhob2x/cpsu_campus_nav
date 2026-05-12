<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginAuthController;
use App\Http\Controllers\DashboardController;

// Public routes (no authentication needed)
Route::get('/', function () {
    return view('welcome');
});

// Route to serve campus GeoJSON data
Route::get('/api/campus-buildings', function () {
    $geojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9336, 10.4605]
                ],
                'properties' => [
                    'name' => 'Main Administration Building',
                    'type' => 'Administrative',
                    'description' => 'University President Office, Registrar, Accounting',
                    'floor' => 3,
                    'rooms' => ['President Office', 'Registrar', 'Accounting', 'Cashier']
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9340, 10.4608]
                ],
                'properties' => [
                    'name' => 'College of Engineering and Computer Studies',
                    'type' => 'Academic',
                    'description' => 'Computer Science, Engineering, IT Department',
                    'floor' => 4,
                    'rooms' => ['CS Lab 1', 'CS Lab 2', 'Engineering Lab', 'Research Center']
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9330, 10.4600]
                ],
                'properties' => [
                    'name' => 'University Library',
                    'type' => 'Facility',
                    'description' => 'Main Library with E-Resources',
                    'floor' => 2,
                    'hours' => '8:00 AM - 8:00 PM'
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9345, 10.4610]
                ],
                'properties' => [
                    'name' => 'College of Arts and Sciences',
                    'type' => 'Academic',
                    'description' => 'Humanities, Social Sciences, Natural Sciences',
                    'floor' => 3
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9328, 10.4603]
                ],
                'properties' => [
                    'name' => 'CPSU Gymnasium',
                    'type' => 'Facility',
                    'description' => 'Indoor Sports Complex',
                    'capacity' => 2000
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9338, 10.4595]
                ],
                'properties' => [
                    'name' => 'College of Agriculture and Forestry',
                    'type' => 'Academic',
                    'description' => 'Agriculture, Forestry, Environmental Studies',
                    'floor' => 2
                ]
            ],
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [122.9332, 10.4612]
                ],
                'properties' => [
                    'name' => 'Student Center',
                    'type' => 'Facility',
                    'description' => 'Student Services, Canteen, Organization Offices'
                ]
            ]
        ]
    ];
    
    return response()->json($geojson);
});

// Authentication routes
Route::get('/login', [LoginAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginAuthController::class, 'login']);
Route::post('/logout', [LoginAuthController::class, 'logout'])->name('logout');

// Protected admin routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/offices', [DashboardController::class, 'offices'])->name('admin.offices');
    Route::get('/admin/processes', [DashboardController::class, 'processes'])->name('admin.processes');
    Route::get('/admin/map-editor', [DashboardController::class, 'mapEditor'])->name('admin.map-editor');
    
    // Office management
    Route::post('/admin/offices/store', [DashboardController::class, 'storeOffice'])->name('admin.offices.store');
    Route::put('/admin/offices/{id}', [DashboardController::class, 'updateOffice'])->name('admin.offices.update');
    Route::delete('/admin/offices/{id}', [DashboardController::class, 'destroyOffice'])->name('admin.offices.destroy');
});

// User dashboard (for non-admin users)
Route::get('/dashboard', [LoginAuthController::class, 'dashboard'])->middleware('auth')->name('dashboard');

Route::get('/directory', function () {
    return view('directory');
});