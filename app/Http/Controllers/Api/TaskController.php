<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = json_encode(Task::orderBy("updated_at", "desc")->get());
        return $tasks;
    }

    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        $task = Task::create($validated);
        return response()->json($task);
    }

    public function show(string $id) {}

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        if ($task->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $task->update($validated);
        return response()->json($task);
    }

    public function destroy(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $result = $task->delete();
        return response()->json($result);
    }
}
