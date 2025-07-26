<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $user = User::all();
        return response ()->json([
            'data' => $user,
            'message' => 'Data User Berhasil Ditampilkan',
            'code' => 200,
        ]);

        if (!$user) {
            return response()->json(['error' => 'Username not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password mismatch'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => $user,
            'name' => $user->name,
            'message' => 'Data User Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

}
