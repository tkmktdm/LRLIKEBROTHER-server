<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = json_encode(Task::orderBy("sort_order", "asc")->where("user_id", auth()->id())->get());
        // $tasks = json_encode(Task::orderBy("updated_at", "desc")->where("user_id", auth()->id())->get());
        // $tasks = json_encode(Task::orderBy("updated_at", "desc")->get());
        return $tasks;
    }

    public function store(StoreTaskRequest $request)
    {
        Log::debug("task store--------");
        Log::debug($request);
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        $task = Task::create($validated);
        return response()->json($task);
    }

    public function show(string $id) {}

    public function update(UpdateTaskRequest $request, Task $task)
    {
        Log::debug("task update--------");
        Log::debug($request);
        $validated = $request->validated();
        $validated["user_id"] = auth()->id();
        if ($task->user_id !== auth()->id()) {
            return response()->json(["message" => "Forbidden"], 403);
        }
        $task->update($validated);
        return response()->json($task);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'task_ids' => ['required', 'array'],
            'task_ids.*' => ['integer'],
        ]);

        foreach ($request->task_ids as $order => $id) {
            Task::where('id', $id)
                ->where('user_id', auth()->id())
                ->update(['sort_order' => $order]);
        }

        return response()->json(['message' => 'ok']);
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
