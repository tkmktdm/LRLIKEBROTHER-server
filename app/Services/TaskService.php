<?php

namespace App\Services;

use App\Models\Task;
use App\Http\Requests\AiTalkHistoryResource;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Models\AiTalkHistory;

class TaskService
{
    /**
     * @param Task
     *  'message' => 'string',
     *  'emotion_data' => 'string',
     *  'select_speaker' => 'integer', // 0:ユーザー, 1:AIエージェント
     *  'user_id' => 'integer',
     *  'ai_agent_id' => 'integer',
     *  'task_id' => 'integer',
     *  'Task_id' => 'integer',
     */
    public function get(int $userId)
    {
        $result = json_encode(Task::orderBy("updated_at", "desc")->get());
        return $result;
    }

    public function create($data)
    {
        $result = Task::create($data);
        return response()->json($result);;
    }

    public function update($data, Task $task)
    {
        $result = $task->update($data);
        return response()->json($result);
    }

    public function delete(Task $Task)
    {
        $result = $Task->delete();
        return response()->json($result);
    }
}
