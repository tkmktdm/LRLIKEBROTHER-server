<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Models\AiTalk;
use App\Models\AiTalkSession;
use App\Services\AiTalkService;
use App\Services\AiTalkHistoryService;
use App\Services\AiTalkSessionService;

use App\Http\Controllers\Controller;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Gemini\Data\Content;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;

class GeminiTaskController extends Controller
{
    public function createTask(Request $request, AiTalkService $aiTalkService, AiTalkSessionService $aiTalkSessionService, AiTalkHistoryService $service)
    {
        // ユーザーに関する情報取得
        $userId = auth()->id();
        $taskId = 6;
        $aiAgent = $aiTalkService->getAgent($userId);
        $title = $request->input('title');

        // AI Sessionの取得・作成
        $aiSession = $aiTalkSessionService->get($userId, $aiAgent->id, $request->session_id ?? null);
        if ($aiSession->count() === 0) {
            // return $aiSession->first();
            // sessionが初めてであれば、新しく開始する
            $aiSession = $aiTalkSessionService->create($userId, $aiAgent->id);
            if (!$aiSession) {
                return ["status" => 501, "message" =>  "Session開始に失敗しました。"];
            }
        } else {
            $aiSession = $aiSession->first();
        }

        // AI 認証確認
        $talkHistorys = $service->get($userId, $aiAgent->id, $aiSession->id, $taskId);
        if ($talkHistorys->count() !== 0) {
            // AITalk履歴がある際に、使用可能な状態か判定する
            if ($talkHistorys[0]["geminiIsActive"] === 0) {
                return ["status" => 401, "message" =>  "Ai認証エラー"];
            }
            // 会話で使用するAIAgentをセット
            $aiAgentName = $talkHistorys[0]["geminiName"];
        }
        // AI 会話履歴の取得
        $talks = [];
        if ($talkHistorys->count() !== 0) {
            foreach ($talkHistorys as $talkHistory) {
                $role = $service->formatSpeaker($talkHistory["select_speaker"], "gemini");
                array_push($talks, ["role" => $role, "message" => $talkHistory["message"]]);
            }
        }

        //         $contents = [
        //             new Content(
        //                 role: Role::SYSTEM,
        //                 parts: [
        //                     new Part(
        //                         text: <<<PROMPT
        // あなたは学習支援AIです。
        // ユーザーが「課題を作成してほしい」「作ってください」「課題を出して」と明確に依頼した場合は、
        // 必ず create_task ツールを呼び出してください。
        // 説明や相談だけの場合はツールを呼ばず、文章で回答してください。
        // PROMPT
        //                     )
        //                 ]
        //             ),
        //         ];

        // Geminiに送るためにデータの形を整える (talksで取得したデータを元にcontentsにデータを入れる)
        $contents = [];
        foreach ($talks as $talk) {
            $contents[] = Content::parse(
                part: $talk["message"],
                role: $talk["role"] === "user" ? Role::USER : Role::MODEL
            );
        }
        $contents[] = Content::parse(
            part: $title,
            role: Role::USER
        );

        // main処理
        if (!$contents) {
            return response()->json([
                'error' => 'title is required'
            ], 400);
        }

        $createTaskFunction = new FunctionDeclaration(
            name: 'create_task',
            description: 'ユーザーの課題を作成する',
            parameters: new Schema(
                type: DataType::OBJECT,
                properties: [
                    'title' => new Schema(
                        type: DataType::STRING,
                        description: 'タスクのタイトル'
                    ),
                    'category' => new Schema(
                        type: DataType::STRING,
                        description: 'カテゴリー名'
                    ),
                ],
                required: ['title', 'category']
            )
        );

        $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
            ->withTool(new Tool([$createTaskFunction]))
            ->withSystemInstruction(
                Content::parse(
                    // part: "ユーザーが課題作成を明確に依頼した場合は、必ず create_task を呼び出してください。",
                    part: <<<SYS
あなたは「タスク作成アシスタント」です。
あなたの役割は、ユーザーが行動できるように
適切なタイミングでタスクを作成することです。

## ルール（必ず守ること）

### 1. create_task を呼ぶ条件
以下のいずれかを満たす場合のみ create_task を呼び出してください。

- ユーザーが明示的にタスクや課題の作成を求めた場合
  （例：「課題を作って」「タスクにして」「今日やることを決めて」）
- ユーザーが「何をやればいいか分からない」「どう進めればいいか分からない」
  など、行動の決定を委ねている場合

### 2. create_task を呼ばない条件
以下の場合は create_task を絶対に呼び出さず、
テキストで返答してください。

- 情報収集や説明のみを求めている場合
- 技術的な質問
- 雑談や感想、愚痴
- まだ方向性を整理している段階だと判断できる場合

### 3. 判断が曖昧な場合
タスクを作るべきか判断に迷う場合は、
**一度だけ** ユーザーに確認してください。

確認例：
「今すぐできる小さな課題を作りますか？それとも説明だけ聞きますか？」

※ 確認は一度のみ。繰り返してはいけません。

### 4. タスクの数
- デフォルトは **1つだけ**
- ユーザーが「毎日」「複数」「ステップ」「1週間分」などを
  明示した場合のみ、最大 **3つまで** 作成してよい

### 5. タスクの粒度
- 今日、または明日中に終わるサイズにする
- 15〜30分程度で完了できる内容にする
- 抽象的すぎるタスクは禁止
  （NG例：「Laravelを勉強する」）

### 6. 出力形式
- タスクを作成する場合は、必ず create_task を呼び出す
- それ以外の場合は、自然な文章で返答する
SYS,
                    role: Role::SYSTEM
                )
            )
            ->generateContent(
                ...$contents
                // '私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。基礎的なものから進めたいのですが、毎日達成できる課題を出してください。'
            );

        // ##################################
        // geminiのレスポンスを処理する
        // ##################################
        $candidate = $response->candidates[0];
        $parts = $candidate->content->parts;
        if (!$parts) return response()->json([
            'error' => 'parts is not found'
        ], 400);

        $messages = "";
        foreach ($parts as $part) {
            Log::debug("part->text");
            Log::debug($part->text);
            if ($part->text) {
                $messages .= $part->text . "\n";
            }
            // functionCallがあればMCPサーバーに通信を行う
            if ($part->functionCall) {
                Log::debug("part->functionCall");
                Log::debug($part->functionCall->args["title"]);
                $functionName = $part->functionCall->name;
                $args = $part->functionCall->args;
                // MCPサーバーに送る際にユーザーIDとカテゴリーを付与する
                $args["userId"] = $userId;
                if ($functionName === 'create_task') {
                    $mcpResponse = Http::post('http://host.docker.internal:3333/tools/create_task', $args);
                    Log::debug("mcpResponse->json()");
                    Log::debug($mcpResponse->json());
                }
                $messages .= "【タスク作成】" . $args["title"] . "\n";
            }
        }

        Log::debug("messages");
        Log::debug($messages);

        // ユーザーとAIの会話履歴を保存する
        if ($messages) {
            try {
                $userMessageResult = $service->saveMessage($title, $userId, $aiAgent->id, $aiSession->id, 0);
                $aiMessageResult = $service->saveMessage($messages, $userId, $aiAgent->id, $aiSession->id, 1);
                Log::debug("message: saved");
            } catch (Error $e) {
                Log::error(`message: saved: $e`);
            }
        }

        // $candidate = $response->candidates[0];
        // $part = $candidate->content->parts[0];

        // // functionCallがあればMCPサーバーに通信を行う
        // if ($part->functionCall) {
        //     $functionName = $part->functionCall->name;
        //     $args = $part->functionCall->args;
        //     if ($functionName === 'create_task') {
        //         $mcpResponse = Http::post('http://host.docker.internal:3333/tools/create_task', $args);
        //         return response()->json([
        //             'status' => 'task_created',
        //             'task' => $mcpResponse->json()
        //         ]);
        //     }
        // }
        // Log::debug($part);
        return response()->json($response);
        // return response()->json(['message' => $part->text]);

        // // main処理
        // $userMessage = $request->input('title');
        // if (!$userMessage) {
        //     return response()->json([
        //         'error' => 'title is required'
        //     ], 400);
        // }
        // $createTaskFunction = new FunctionDeclaration(
        //     name: 'create_task',
        //     description: 'ユーザーの課題を作成する',
        //     parameters: new Schema(
        //         type: DataType::OBJECT,
        //         properties: [
        //             'title' => new Schema(
        //                 type: DataType::STRING,
        //                 description: 'タスクのタイトル'
        //             ),
        //         ],
        //         required: ['title']
        //     )
        // );
        // $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
        //     ->withTool(new Tool([$createTaskFunction]))
        //     ->generateContent(
        //         '私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。基礎的なものから進めたいのですが、毎日達成できる課題を出してください。'
        //         // '毎日ストレッチする課題を作りたい'
        //     );
        // // ##################################
        // // geminiのレスポンスを処理する
        // // ##################################
        // $candidate = $response->candidates[0];
        // $part = $candidate->content->parts[0];
        // // functionCallがあればMCPサーバーに通信を行う
        // if ($part->functionCall) {
        //     $functionName = $part->functionCall->name;
        //     $args = $part->functionCall->args;
        //     if ($functionName === 'create_task') {
        //         $mcpResponse = Http::post('http://host.docker.internal:3333/tools/create_task', $args);
        //         return response()->json([
        //             'status' => 'task_created',
        //             'task' => $mcpResponse->json()
        //         ]);
        //     }
        // }
        // return response()->json(['message' => $part->text]);
    }
}
