<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiAgentRequest;
use App\Http\Requests\UpdateAiAgentRequest;
use App\Models\AiAgent;

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
    public function store(StoreAiAgentRequest $request, AiAgent $aiAgents)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        $aiAgents = AiAgent::create($validated);
        return response()->json($aiAgents);
    }
    public function update(UpdateAiAgentRequest $request, AiAgent $aiAgents)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        if ($aiAgents->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $aiAgents->update($validated);
        return response()->json($aiAgents);
    }
    public function destroy(AiAgent $aiAgents)
    {
        if ($aiAgents->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $result = $aiAgents->delete();
        return response()->json($result);
    }

    public function create() {}
    public function show(string $id) {}
    public function edit(string $id) {}
}
