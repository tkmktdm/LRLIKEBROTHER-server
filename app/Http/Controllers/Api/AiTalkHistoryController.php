<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiTalkHistory;
use Illuminate\Http\Request;

class AiTalkHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = json_encode(AiTalkHistory::orderBy("updated_at", "desc")->get());
        return $tasks;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tasks = json_encode(AiTalkHistory::orderBy("updated_at", "desc")->get($id));
        return $tasks;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
