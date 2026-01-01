<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Requests\AiTalkHistoryResource;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Models\AiAgent;
use App\Models\AiTalk;
use App\Models\AiTalkHistory;
use App\Models\AiTalkSession;
use App\Models\Task;
use Error;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AiTalkSessionService
{
    public function get($userId, $aiAgentId, $sessionId = null)
    {
        $query = [
            "user_id" => $userId,
            "ai_agent_id" => $aiAgentId,
        ];
        if ($sessionId) {
            $query["id"] = $sessionId;
        }
        $aiSession = AiTalkSession::where($query)->first();
        return $aiSession;
    }

    public function create($userId, $aiAgentId)
    {
        // $categories = Category::create($data);
        // return response()->json($categories);
        $aiSession = AiTalkSession::create([
            "user_id" => $userId,
            "ai_agent_id" => $aiAgentId
        ]);
        return $aiSession;
    }

    public function delete(AiTalkSession $aiTalkSession)
    {
        $result = $aiTalkSession->delete();
        return response()->json($result);
    }

    /**
     * @param AiTalk
     *  'message' => 'string',
     *  'emotion_data' => 'string',
     *  'select_speaker' => 'integer', // 0:ユーザー, 1:AIエージェント
     *  'user_id' => 'integer',
     *  'ai_agent_id' => 'integer',
     *  'task_id' => 'integer',
     *  'category_id' => 'integer',
     */
    public function formatAiTalkData($data, $userId)
    {
        /*
        // categoryの作成
        {
            "name": "バックエンド技術向上トレーニング",
            "description": "LaravelとTypeScriptを毎日学習し、バックエンドエンジニアとしての技術力向上を目指すトレーニングプランです。"
        }
        // taskの作成
        [{
            "name": "Laravel Eloquentリレーション学習",
            "description": "異なる2つのモデルを作成し、Eloquentのリレーション（例：hasMany, belongsTo）を定義してデータ取得を試す。",
            "start_date": 1765696405, //"2025-12-09 22:06:09",
            "end_date": 1765696405, //"2025-12-10 22:06:09",
            "difficulty": "medium"
        }]
        */
        try {
            Log::debug("transaction start");

            DB::transaction(function () use ($data, $userId) {
                $baseFormatService = new BaseFormatService();
                $categoryData = $data["category"];
                if ($categoryData === null) {
                    throw new \Exception('category is required');
                }
                $taskDatas = $data["tasks"];
                if ($taskDatas === null) {
                    throw new \Exception('tasks is required');
                }

                Log::debug("category create start");
                $category = Category::create([
                    "name" => $categoryData["name"],
                    "description" => $categoryData["description"] ?? null,
                    "user_id" => $userId,
                ]);

                Log::debug("tasks create start");
                // Log::debug($category);
                foreach ($taskDatas as $taskData) {
                    Log::debug($baseFormatService->dateToTimestamp($taskData["end_date"]));
                    $task = Task::create([
                        'title' => $taskData["name"],
                        'notes' => $taskData["description"],
                        'start_date' => $taskData["start_date"],
                        'end_date' => $taskData["end_date"],
                        // 'start_date' => $baseFormatService->dateToTimestamp($taskData["start_date"]),
                        // 'end_date' => $baseFormatService->dateToTimestamp($taskData["end_date"]),
                        "user_id" => $userId,
                        'category_id' => $category->id,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
        return true;
    }

    public function createTask() {}
}
