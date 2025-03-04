<?php

use Diagonal\DbLogger\App\Models\DbLogger;
use Diagonal\DbLogger\App\Observers\GlobalModelObserver;
use Diagonal\DbLogger\Tests\Models\User;
use Diagonal\DbLogger\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->createdUser = createUser();
});


test('it logs user creation', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Check if the creation was logged
    $log = DbLogger::where('model', User::class)
        ->where('model_id', $user->id)
        ->where('action', 'INSERT')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->model)->toBe(User::class)
        ->and($log->model_id)->toBe($user->id);
});

test('it logs user update', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // Update the user
    $user->update(['name' => 'Updated Name']);

    // Check if the update was logged
    $log = DbLogger::where('model', User::class)
        ->where('model_id', $user->id)
        ->where('action', 'UPDATE')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->model)->toBe(User::class)
        ->and($log->model_id)->toBe($user->id);
});

test('it logs user deletion', function () {
    // Create a user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $userId = $user->id;

    // Delete the user
    $user->delete();

    // Check if the deletion was logged
    $log = DbLogger::where('model', User::class)
        ->where('model_id', $userId)
        ->where('action', 'DELETE')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->model)->toBe(User::class)
        ->and($log->model_id)->toBe($userId);
});


test('check if RESTORE event is correctly loaded', function () {
    $createdUser = createUser();
    $userClass = get_class($createdUser);

    $createdUserId = $createdUser->id;
    $createdUser->delete();
    $createdUser->restore();

    $loggedData = DbLogger::where('model_id', $createdUserId)
        ->where('model', $userClass)
        ->where('action', GlobalModelObserver::RESTORED)
        ->get();

    expect($loggedData->count())->toBeOne();
});

test('check if FORCE_DELETED event is correctly loaded', function () {
    $createdUser = createUser();
    $userClass = get_class($createdUser);

    $createdUserId = $createdUser->id;
    $createdUser->delete();
    $createdUser->forceDelete();

    $loggedData = DbLogger::where('model_id', $createdUserId)
        ->where('model', $userClass)
        ->where('action', GlobalModelObserver::FORCE_DELETED)
        ->get();

    expect($loggedData->count())->toBeOne();
});
