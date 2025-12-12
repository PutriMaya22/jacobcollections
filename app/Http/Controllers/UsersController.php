<?php

// app/Http/Controllers/UsersController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all(); // Ambil semua data user
        return view('data_users.index', compact('users'));
    }
    public function destroy(User $data_user)
    {
        $data_user->delete();
        return redirect()->route('data_users.index')
                         ->with('success', 'User berhasil dihapus');
    }

}