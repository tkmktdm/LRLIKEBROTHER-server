<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Requests\AiTalkHistoryResource;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Models\AiTalkHistory;
use Illuminate\Support\Facades\DB;
use Gemini\Enums\Role;
use Illuminate\Support\Facades\Log;

use Error;

class AiTalkHistoryService
{
    /**
     * @param AiTalkHistory
     *  'message' => 'string',
     *  'emotion_data' => 'string',
     *  'select_speaker' => 'integer', // 0:ユーザー, 1:AIエージェント
     *  'user_id' => 'integer',
     *  'ai_agent_id' => 'integer',
     *  'task_id' => 'integer',
     *  'category_id' => 'integer',
     */
    public function get(int $userId, int $aiAgentId, int $aiSessionId, int $taskId = 0)
    {
        try {
            $talkHistory = AiTalkHistory::where([
                "ai_talk_histories.user_id" => $userId,
                "ai_talk_histories.ai_agent_id" => $aiAgentId,
                "ai_talk_histories.ai_talk_session_id" => $aiSessionId,
                // "ai_talk_histories.task_id" => $taskId,
            ])
                ->join("ai_agents", "ai_talk_histories.ai_agent_id", "=", "ai_agents.id")
                // ->join("categories", "ai_talk_histories.category_id", "=", "categories.id")
                ->orderBy("created_at", "asc")
                ->select([
                    "ai_talk_histories.*",
                    // "categories.name as categoryName",
                    // "ai_agents.*",
                    "ai_agents.name as geminiName",
                    "ai_agents.version as geminiVersion",
                    "ai_agents.is_active as geminiIsActive",
                ])
                ->get();

            Log::debug("histroy DB Access");
            Log::debug($talkHistory);

            return $talkHistory;
        } catch (Error $e) {
            return false;
        }
        // $talkHistory = AiTalkHistory::where([
        //     "user_id" => $userId,
        //     "task_id" => $taskId,
        // ])->orderBy("created_at", "asc")->get();
        // return $talkHistory;
    }

    public function saveMessage($message, $userId, $aiAgentId, $aiSessionId, $role)
    {
        try {
            $flg = $role === Role::USER ? 0 : 1;
            DB::transaction(function () use ($message, $userId, $aiAgentId, $aiSessionId, $flg) {
                AiTalkHistory::create([
                    'message' => $message,
                    'select_speaker' => $flg,
                    'user_id' => $userId,
                    'ai_agent_id' => $aiAgentId,
                    // 仮の値
                    'ai_talk_session_id' => $aiSessionId,
                    // 'task_id',
                    // 'category_id',
                    // 'emotion_data',
                ]);
                //  $task = Task::create([
                //     'title' => $taskData["name"],
                //     'notes' => $taskData["description"],
                //     'start_date' => $taskData["start_date"],
                //     'end_date' => $taskData["end_date"],
                //     // 'start_date' => $baseFormatService->dateToTimestamp($taskData["start_date"]),
                //     // 'end_date' => $baseFormatService->dateToTimestamp($taskData["end_date"]),
                //     "user_id" => $userId,
                //     'category_id' => $category->id,
                // ]);
            });
            return true;
        } catch (Error $e) {
            return false;
        }
    }

    public function formatSpeaker(int $speaker, $model)
    {
        /**
         * llm: system
         * gemini: model
         */
        $ai_config = config("ai-model");
        $role = ($speaker === 0) ? "user" : $ai_config[$model];
        return $role;
    }
}
