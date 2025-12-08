<?php

namespace App\Http\Controllers;

use App\Models\AiTalk;
use App\Models\AiTalkHistory;
use App\Models\Category;
use App\Services\AiTalkHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;


class AiTalkController extends Controller
{

  // Gemini
  public function geminiGenerateTalk(Request $request, AiTalkHistoryService $service)
  {
    // $talkHistory = AiTalkHistory::all();
    $userId = auth()->id();
    $taskId = 6;
    // $taskId = $request["task_id"];
    $talkHistorys = $service->get($userId, $taskId);
    // AI使用制限を確認
    if ($talkHistorys[0]["geminiIsActive"] === 0) {
      return ["status" => 401, "message" =>  "認証エラー"];
    }
    // return $talkHistory;
    $talks = [];
    // 会話データ
    // foreach ($talkHistorys as $talkHistory) {
    //     $role = $service->formatSpeaker($talkHistory["select_speaker"], "gemini");
    //     array_push($talk, ["role" => $role, "part" => $talkHistory["message"]]);
    // }
    // template
    $conversationSummary = $talkHistorys || "ユーザーの目標を最初に質問してください。";
    $lastMessage = data_get($talkHistorys, (count($talkHistorys) - 1) . '.message', '');


    array_unshift(
      $talks,
      [
        "role" => "user",
        "message" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。通常の会話は50文字程度で会話を返してください。"
      ],
      [
        "role" => "user",
        "message" => <<<PROMPT
会話コンテキストをもとに、ユーザーがタスクを作成して欲しいと依頼かやりたいことが明確になった際に、ユーザーが必要とするタスクを作成してください。その際の学習用のカテゴリーとタスクを**必ず** JSON 形式で返してください。

会話要約:
      {$conversationSummary}

会話履歴（最新）:
      {$lastMessage}
カテゴリーとタスクの出力スキーマ:
{
  "category": {
    "name": "文字列 (必須)",
    "description": "文字列 (任意)"
  },
  "tasks": [
    {
      "name": "文字列 (必須)",
      "description": "文字列 (任意)",
      "start_date": "YYYY-MM-DD (任意)",
      "end_date": "YYYY-MM-DD (任意)",
      "difficulty": "easy|medium|hard (任意)"
    }
  ]
}

追加ルール:
- タスクは3件以上、7件以内を推奨する（ただしユーザーの希望があれば調整）。
- 日付を付ける場合は「今日から2週間以内」に収める。
- 必ず上記JSONのみを返す。
PROMPT
      ]

      // ["role" => "user", "message" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
    );
    // [{
    //     "role": "system",
    //     "content": "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"
    // },{
    //     "role": "user",
    //     "content": "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"
    // }]

    $histories = [];
    foreach ($talks as $talk) {
      $histories[] = Content::parse(
        part: $talk["message"],
        // role: $service->formatSpeaker($talk["select_speaker"], "gemini"),
        role: $talk["role"] === "user" ? Role::USER : Role::MODEL
      );
    }
    // return $histories;
    $chat = Gemini::generativeModel(model: $talkHistorys[0]["geminiName"])
      ->startChat(history: $histories);
    // $chat = Gemini::generativeModel(model: $talkHistorys[0]["geminiName"])
    //     ->startChat(history: [
    //         Content::parse(part: 'The stories you write about what I have to say should be one line. Is that clear?'),
    //         Content::parse(part: 'Yes, I understand. The stories I write about your input should be one line long.', role: Role::MODEL)
    //     ]);
    // $response = $chat->sendMessage('Create a story set in a quiet village in 1600s France');
    // echo $response->text(); // Amidst rolling hills and winding cobblestone streets, the tranquil village of Beausoleil whispered tales of love, intrigue, and the magic of everyday life in 17th century France.
    // $response = $chat->sendMessage('Rewrite the same story in 1600s England');
    // echo $response->text(); // In the heart of England's lush countryside, amidst emerald fields and thatched-roof cottages, the village of Willowbrook unfolded a tapestry of love, mystery, and the enchantment of ordinary days in the 17th century.

    $response = $chat->sendMessage('私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。');
    // echo $response->text(); // In the heart of England's lush countryside, amidst emerald fields and thatched-roof cottages, the village of Willowbrook unfolded a tapestry of love, mystery, and the enchantment of ordinary days in the 17th century.
    // echo $chat->text();

    return response()->json([
      'text' => $response->text(),
      'raw'  => method_exists($response, 'toArray') ? $response->toArray() : (array)$response,
    ]);
    // return $response;
  }
  /** レスポンス結果
{
    "text": "```json\n{\n  \"category\": {\n    \"name\": \"LaravelとTypeScript基礎\",\n    \"description\": \"バックエンドエンジニアとして必要なLaravelとTypeScriptの基礎を固めるための、毎日少しずつ達成できる学習プランだよ！\"\n  },\n  \"tasks\": [\n    {\n      \"name\": \"Laravel開発環境構築＆Hello World\",\n      \"description\": \"DockerやLaravel Sailを使って開発環境をセットアップし、基本的なルーティングで「Hello World」を表示してみよう！\",\n      \"start_date\": \"2023-10-27\",\n      \"end_date\": \"2023-10-27\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"TypeScriptの基本型マスター\",\n      \"description\": \"string, number, booleanなどの基本的な型を学び、変数宣言や関数引数に型アノテーションを使ってみよう！\",\n      \"start_date\": \"2023-10-28\",\n      \"end_date\": \"2023-10-28\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"Laravelルーティングとコントローラー\",\n      \"description\": \"複数のルーティングを作成し、コントローラーを通じてビューにデータを渡す基本的な流れを理解しよう！\",\n      \"start_date\": \"2023-10-29\",\n      \"end_date\": \"2023-10-29\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"TypeScript関数とインターフェース\",\n      \"description\": \"関数への型定義や、オブジェクトの構造を定義するインターフェースの基本的な使い方を学んでみよう！\",\n      \"start_date\": \"2023-10-30\",\n      \"end_date\": \"2023-10-30\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"Laravel Eloquent ORM入門\",\n      \"description\": \"マイグレーションでデータベーステーブルを作成し、Eloquentモデルを使ってデータの取得や登録を試してみよう！\",\n      \"start_date\": \"2023-10-31\",\n      \"end_date\": \"2023-10-31\",\n      \"difficulty\": \"easy\"\n    }\n  ]\n}\n```",
    "raw": {
        "candidates": [
            {
                "content": {
                    "parts": [
                        {
                            "text": "```json\n{\n  \"category\": {\n    \"name\": \"LaravelとTypeScript基礎\",\n    \"description\": \"バックエンドエンジニアとして必要なLaravelとTypeScriptの基礎を固めるための、毎日少しずつ達成できる学習プランだよ！\"\n  },\n  \"tasks\": [\n    {\n      \"name\": \"Laravel開発環境構築＆Hello World\",\n      \"description\": \"DockerやLaravel Sailを使って開発環境をセットアップし、基本的なルーティングで「Hello World」を表示してみよう！\",\n      \"start_date\": \"2023-10-27\",\n      \"end_date\": \"2023-10-27\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"TypeScriptの基本型マスター\",\n      \"description\": \"string, number, booleanなどの基本的な型を学び、変数宣言や関数引数に型アノテーションを使ってみよう！\",\n      \"start_date\": \"2023-10-28\",\n      \"end_date\": \"2023-10-28\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"Laravelルーティングとコントローラー\",\n      \"description\": \"複数のルーティングを作成し、コントローラーを通じてビューにデータを渡す基本的な流れを理解しよう！\",\n      \"start_date\": \"2023-10-29\",\n      \"end_date\": \"2023-10-29\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"TypeScript関数とインターフェース\",\n      \"description\": \"関数への型定義や、オブジェクトの構造を定義するインターフェースの基本的な使い方を学んでみよう！\",\n      \"start_date\": \"2023-10-30\",\n      \"end_date\": \"2023-10-30\",\n      \"difficulty\": \"easy\"\n    },\n    {\n      \"name\": \"Laravel Eloquent ORM入門\",\n      \"description\": \"マイグレーションでデータベーステーブルを作成し、Eloquentモデルを使ってデータの取得や登録を試してみよう！\",\n      \"start_date\": \"2023-10-31\",\n      \"end_date\": \"2023-10-31\",\n      \"difficulty\": \"easy\"\n    }\n  ]\n}\n```"
                        }
                    ],
                    "role": "model"
                },
                "finishReason": "STOP",
                "safetyRatings": [],
                "citationMetadata": {
                    "citationSources": []
                },
                "tokenCount": null,
                "index": 0,
                "avgLogprobs": null,
                "groundingAttributions": [],
                "groundingMetadata": null,
                "logprobsResult": null,
                "urlRetrievalMetadata": null
            }
        ],
        "promptFeedback": null,
        "usageMetadata": {
            "promptTokenCount": 301,
            "candidatesTokenCount": 544,
            "totalTokenCount": 2729,
            "cachedContentTokenCount": null,
            "toolUsePromptTokenCount": null,
            "thoughtsTokenCount": 1884,
            "promptTokensDetails": [
                {
                    "tokenCount": 301,
                    "modality": "TEXT"
                }
            ],
            "cacheTokensDetails": [],
            "candidatesTokensDetails": [],
            "toolUsePromptTokensDetails": []
        },
        "modelVersion": "gemini-2.5-flash"
    }
}

   */





  // LLM(AI)
  /**
   * AI Talk response streming
   */
  // public function allResponseTalkEndWait(AiTalk $aiTalk)
  // {
  //     $talkHistory = [
  //         ["role" => "system","content" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"],
  //         ["role" => "user","content" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
  //     ];
  //     $stream = Http::withOptions([
  //         'stream' => true,
  //         'timeout' => 0, // 無制限
  //     ])->withHeaders([
  //         'Content-Type' => 'application/json',
  //     ])->send('POST', 'http://ollama:11434/api/chat', [
  //         'json' => [
  //             'model' => 'aoi',
  //             'messages' => $talkHistory,
  //             'stream' => true,
  //         ],
  //     ]);

  //     return response()->stream(function () use ($stream) {
  //         foreach ($stream->toPsrResponse()->getBody() as $chunk) {
  //             echo $chunk;
  //             ob_flush();
  //             flush();
  //         }
  //     }, 200, [
  //         'Content-Type' => 'text/event-stream',
  //         'Cache-Control' => 'no-cache',
  //         'X-Accel-Buffering' => 'no',
  //     ]);
  // }

  /**
    // レスポンスの形
    {
        "model":"aoi",
        "created_at":"2025-11-09T09:49:00.629938475Z",
        "message": {
                "role":"assistant",
                "content":"やったー！LaravelとTypescript、素敵ですね！😊 毎日少しずつ、確実にレベルアップを目指しましょう！\n\n
                今日の課題：\n\n 
                Typescript: 既存のTypescriptファイルに、簡単な型定義を追加してみよう！（例：文字列、数値、booleanなど）\n 
                Laravel: Laravelの簡単なコントローラーを作成し、簡単なAPIエンドポイントを実装してみよう！\n\n
                応援してるよ！🔥"
        },
        "done":true,
        "done_reason":"stop",
        "total_duration":44620222134,
        "load_duration":489331301,
        "prompt_eval_count":61,
        "prompt_eval_duration":866502969,
        "eval_count":88,
        "eval_duration":43261282741
    }
   */

  // // ユーザーからの会話を受けた際に通常会話用のAIエージェントを使用する
  // public function generateTalk(Request $request, AiTalkHistoryService $service)
  // {
  //     // $talkHistory = AiTalkHistory::all();
  //     $userId = auth()->id();
  //     $taskId = 6;
  //     // $taskId = $request["task_id"];
  //     $talkHistory = $service->get($userId, $taskId);
  //     return [
  //         $talkHistory,
  //         $userId,
  //     ];
  //     // $talkHistory = [
  //     //     ["role" => "system", "content" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"],
  //     //     ["role" => "user", "content" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
  //     // ];
  //     // $response = Http::timeout(60)->post('http://ollama:11434/api/chat', [
  //     //     'model' => 'aoi',
  //     //     'messages' => $talkHistory,
  //     //     'stream' => false,
  //     // ]);
  //     // if ($response["done"] === "true" && $response["message"]) {
  //     // }
  //     // return $response;
  //     // Step1
  //     // ユーザーとの会話履歴を取得する

  //     // Step2
  //     // AIエージェントに会話内容を送りレスポンスを受信する

  //     // Step3
  //     // 受信したレスポンスをもとにタスク生成用のAIエージェントにデータを送り生成をする

  //     // Step4
  //     // 生成したタスクをAIエージェントに読み込ませてユーザーに会話ベースで返答する

  // }

  // ユーザーからの会話を受けた際に通常会話用のAIエージェントを使用する
  public function generateTalk(Request $request, AiTalkHistoryService $service)
  {
    // $talkHistory = AiTalkHistory::all();
    $userId = auth()->id();
    $taskId = 6;
    // $taskId = $request["task_id"];
    $talkHistorys = $service->get($userId, $taskId);
    $talk = [];

    // 会話データ
    // foreach ($talkHistorys as $talkHistory) {
    //     $role = $service->formatSpeaker($talkHistory["select_speaker"]);
    //     array_push($talk, ["role" => $role, "content" => $talkHistory["message"]]);
    // }

    // template
    array_push(
      $talk,
      ["role" => "system", "content" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。50文字程度返してください。"],
      ["role" => "user", "content" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
    );
    // [{
    //     "role": "system",
    //     "content": "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"
    // },{
    //     "role": "user",
    //     "content": "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"
    // }]
    // return $talk;

    $prompt = `
あなたは「タスク作成AI」です。以下の会話コンテキストをもとに、学習用のカテゴリーとタスクを**必ず** JSON 形式で返してください。JSON 以外の出力は認めません。

会話要約:
[要約テキストここに]

会話履歴（最新）:
1) User: "プログラミングスキルが低いから勉強がしたいです。"
2) AI: "どの言語やりたいですか。"
3) User: "Go言語です。"
4) AI: "変数の宣言や型の定義方法からすすめていきましょう。"

出力スキーマ:
{
  "category": {
    "name": "文字列 (必須)",
    "description": "文字列 (任意)"
  },
  "tasks": [
    {
      "name": "文字列 (必須)",
      "description": "文字列 (任意)",
      "start_date": "YYYY-MM-DD (任意)",
      "end_date": "YYYY-MM-DD (任意)",
      "difficulty": "easy|medium|hard (任意)"
    }
  ]
}

追加ルール:
- タスクは3件以上、7件以内を推奨する（ただしユーザーの希望があれば調整）。
- 日付を付ける場合は「今日から2週間以内」に収める。
- 必ず上記JSONのみを返す。
`;


    $response = Http::timeout(60)->post('http://ollama:11434/api/chat', [
      'model' => 'aoi',
      'messages' => $talk,
      'stream' => false,
      'temperature' => 0.1,
      'prompt' => $prompt,
    ]);

    // DB::transaction(function () use ($userId, $response) {
    // try {
    // if ($talkHistorys === []) {
    //     $category = Category::create([
    //         "user_id" => $userId,
    //         "name" => $response['category']['name'],
    //         "description" => $response['category']['description'] ?? null,
    //     ]);
    // }
    // } catch($e) {
    //     return $e;
    // }
    // });

    // if ($response["done"] === "true" && $response["message"]) {
    // }
    // if (!$this->fallbackGenerate($summary))
    return $response;

    // Step1
    // ユーザーとの会話履歴を取得する

    // Step2
    // AIエージェントに会話内容を送りレスポンスを受信する

    // Step3
    // 受信したレスポンスをもとにタスク生成用のAIエージェントにデータを送り生成をする

    // Step4
    // 生成したタスクをAIエージェントに読み込ませてユーザーに会話ベースで返答する

  }

  // タスク生成用のAIエージェントを使用する
  public function taskCreateTalk(Request $request)
  {
    $talkHistory = AiTalk::all();
    return $talkHistory;
    $talkHistory = [
      ["role" => "system", "content" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"],
      ["role" => "user", "content" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
    ];
    $response = Http::timeout(60)->post('http://ollama:11434/api/chat', [
      'model' => 'aoi',
      'messages' => $talkHistory,
      'stream' => false,
    ]);
    if ($response["done"] === "true" && $response["message"]) {
    }
    return $response;
  }
}
