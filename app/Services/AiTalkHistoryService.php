<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Requests\AiTalkHistoryResource;
use App\Http\Requests\StoreAiTalkHistoryRequest;
use App\Models\AiTalkHistory;

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
    public function get(int $userId, int $taskId)
    {
        $talkHistory = AiTalkHistory::where([
            "ai_talk_histories.user_id" => $userId,
            "ai_talk_histories.task_id" => $taskId,
        ])->join("categories", "ai_talk_histories.category_id", "=", "categories.id")
            ->join("ai_agents", "ai_talk_histories.ai_agent_id", "=", "ai_agents.id")
            ->orderBy("created_at", "asc")
            ->select([
                "ai_talk_histories.*",
                "categories.name as categoryName",
                // "ai_agents.*",
                "ai_agents.name as geminiName",
                "ai_agents.version as geminiVersion",
                "ai_agents.is_active as geminiIsActive",
            ])
            ->get();
        return $talkHistory;
        // $talkHistory = AiTalkHistory::where([
        //     "user_id" => $userId,
        //     "task_id" => $taskId,
        // ])->orderBy("created_at", "asc")->get();
        // return $talkHistory;
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
