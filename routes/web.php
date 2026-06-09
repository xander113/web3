<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GameServerController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/ping', [HomeController::class, 'ping'])->name('ping');

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/2fa', [AuthController::class, 'show2fa'])->name('two-factor.verify');
    Route::post('/2fa', [AuthController::class, 'verify2fa']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Profile & users (public)
Route::get('/profile/{id}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/friends/list/{id}', [FriendController::class, 'showFriends'])->name('friends.list');

// Forum (public read)
Route::prefix('forum')->name('forum.')->group(function () {
    Route::get('/', [ForumController::class, 'index'])->name('index');
    Route::get('/f/{id}', [ForumController::class, 'showForum'])->name('show');
    Route::get('/t/{id}', [ForumController::class, 'showTopic'])->name('topic.show');
});

// Catalog (public read)
Route::prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/', [CatalogController::class, 'index'])->name('index');
    Route::get('/{id}', [CatalogController::class, 'show'])->name('show');
});

// Groups (public read)
Route::get('/groups/{id}', [GroupController::class, 'show'])->name('groups.show');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings/about', [SettingsController::class, 'updateAbout'])->name('settings.about');
    Route::patch('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password');
    Route::get('/settings/2fa/setup', [SettingsController::class, 'setup2fa'])->name('settings.2fa.setup');
    Route::post('/settings/2fa/enable', [SettingsController::class, 'enable2fa'])->name('two-factor.enable');
    Route::post('/settings/2fa/disable', [SettingsController::class, 'disable2fa'])->name('two-factor.disable');

    // Friends
    Route::get('/friends', [FriendController::class, 'index'])->name('friends.index');
    Route::post('/friends/send/{id}', [FriendController::class, 'send'])->name('friends.request');
    Route::post('/friends/accept/{id}', [FriendController::class, 'accept'])->name('friends.accept');
    Route::post('/friends/decline/{id}', [FriendController::class, 'decline'])->name('friends.decline');
    Route::delete('/friends/remove/{id}', [FriendController::class, 'remove'])->name('friends.remove');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/new/{userId}', [MessageController::class, 'create'])->name('messages.create');
    Route::get('/messages/{id}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::delete('/messages/{id}', [MessageController::class, 'destroy'])->name('messages.destroy');

    // Forum (write)
    Route::get('/forum/f/{forumId}/new', [ForumController::class, 'createTopic'])->name('forum.topic.create');
    Route::post('/forum/f/{forumId}/topic', [ForumController::class, 'storeTopic'])->name('forum.topic.store');
    Route::post('/forum/t/{topicId}/reply', [ForumController::class, 'storeReply'])->name('forum.topic.reply');
    Route::delete('/forum/t/{id}', [ForumController::class, 'deleteTopic'])->name('forum.topic.delete');
    Route::delete('/forum/r/{id}', [ForumController::class, 'deleteReply'])->name('forum.reply.delete');

    // Catalog (buy/upload)
    Route::post('/catalog/{id}/buy', [CatalogController::class, 'buy'])->name('catalog.buy');
    Route::get('/catalog/upload', [CatalogController::class, 'create'])->name('catalog.upload');
    Route::post('/catalog', [CatalogController::class, 'store'])->name('catalog.store');

    // Character
    Route::get('/character', [CharacterController::class, 'index'])->name('character.index');
    Route::post('/character/equip/{id}', [CharacterController::class, 'equip'])->name('character.equip');
    Route::post('/character/unequip/{id}', [CharacterController::class, 'unequip'])->name('character.unequip');
    Route::patch('/character/colors', [CharacterController::class, 'updateColors'])->name('character.colors');

    // Groups (write)
    Route::get('/groups/new', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::post('/groups/{id}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::post('/groups/{id}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    // Game Servers
    Route::get('/games/new', [GameServerController::class, 'create'])->name('games.create');
    Route::post('/games', [GameServerController::class, 'store'])->name('games.store');

    // Admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users/{id}/ban', [AdminController::class, 'ban'])->name('users.ban');
        Route::post('/users/{id}/unban', [AdminController::class, 'unban'])->name('users.unban');
        Route::get('/assets', [AdminController::class, 'assets'])->name('assets');
        Route::post('/assets/{id}/approve', [AdminController::class, 'approveAsset'])->name('assets.approve');
        Route::post('/assets/{id}/decline', [AdminController::class, 'declineAsset'])->name('assets.decline');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::post('/reports/{id}/resolve', [AdminController::class, 'resolveReport'])->name('reports.resolve');
        Route::post('/reports/{id}/dismiss', [AdminController::class, 'resolveReport'])->name('reports.dismiss');
    });
});
