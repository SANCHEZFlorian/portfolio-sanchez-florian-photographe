<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/projets', [PublicController::class, 'projects'])->name('projects');
Route::get('/projets/{slug}', [PublicController::class, 'projectShow'])->name('projects.show');
Route::get('/galerie', [PublicController::class, 'gallery'])->name('gallery');
Route::get('/a-propos', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'contactStore'])->name('contact.store');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return \Inertia\Inertia::render('Dashboard', [
        'stats' => [
            'projects' => \App\Models\Project::count(),
            'photos' => \App\Models\Photo::count(),
            'categories' => \App\Models\Category::count(),
            'unreadMessages' => \App\Models\Contact::where('is_read', false)->count(),
        ],
        'recentContacts' => \App\Models\Contact::latest()->take(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Categories
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Projects
    Route::resource('projects', \App\Http\Controllers\Admin\ProjectController::class);

    // Photos (nested under projects)
    Route::post('projects/{project}/photos', [\App\Http\Controllers\Admin\PhotoController::class, 'store'])->name('projects.photos.store');
    Route::put('projects/{project}/photos/{photo}', [\App\Http\Controllers\Admin\PhotoController::class, 'update'])->name('projects.photos.update');
    Route::delete('projects/{project}/photos/{photo}', [\App\Http\Controllers\Admin\PhotoController::class, 'destroy'])->name('projects.photos.destroy');
    Route::post('projects/{project}/photos/reorder', [\App\Http\Controllers\Admin\PhotoController::class, 'reorder'])->name('projects.photos.reorder');

    // Standalone photos (En vrac)
    Route::get('photos', [\App\Http\Controllers\Admin\StandalonePhotoController::class, 'index'])->name('photos.index');
    Route::post('photos', [\App\Http\Controllers\Admin\StandalonePhotoController::class, 'store'])->name('photos.store');
    Route::put('photos/{photo}', [\App\Http\Controllers\Admin\StandalonePhotoController::class, 'update'])->name('photos.update');
    Route::delete('photos/{photo}', [\App\Http\Controllers\Admin\StandalonePhotoController::class, 'destroy'])->name('photos.destroy');
    Route::post('photos/reorder', [\App\Http\Controllers\Admin\StandalonePhotoController::class, 'reorder'])->name('photos.reorder');

    // Contacts
    Route::get('contacts', [\App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contacts.index');
    Route::patch('contacts/{contact}/toggle-read', [\App\Http\Controllers\Admin\ContactController::class, 'toggleRead'])->name('contacts.toggleRead');
    Route::delete('contacts/{contact}', [\App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('contacts.destroy');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [\App\Http\Controllers\Admin\SettingController::class, 'store'])->name('settings.store');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
