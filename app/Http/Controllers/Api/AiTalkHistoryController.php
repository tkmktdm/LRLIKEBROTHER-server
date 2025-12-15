<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Http\Requests\UpdateAiTalkHistoryRequest;
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
    public function store(StoreAiTalkHistoryRequest $request, AiTalkHistory $aiTalkHistory)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        $history = AiTalkHistory::create($validated);
        return response()->json($history);
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
    public function update(UpdateAiTalkHistoryRequest $request, AiTalkHistory $aiTalkHistory)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        if ($aiTalkHistory->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $aiTalkHistory->update($validated);
        return response()->json($aiTalkHistory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
