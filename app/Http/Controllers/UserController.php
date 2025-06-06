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
