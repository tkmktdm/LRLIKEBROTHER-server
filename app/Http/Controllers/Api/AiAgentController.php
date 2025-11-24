<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aiAgents = json_encode(AiAgent::orderBy("updated_at", "desc")->get());
        return $aiAgents;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, AiAgent $aiAgents)
    {
        $aiAgents = AiAgent::create($request->all());
        return response()->json($aiAgents);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AiAgent $aiAgents)
    {
        Log::info($request);
        $aiAgents->update($request->all());
        return response()->json($aiAgents);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
