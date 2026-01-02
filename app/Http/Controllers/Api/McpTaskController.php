<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\Schema;
use Gemini\Enums\DataType;
use Gemini\Data\Content;
use Gemini\Data\Part;
use Gemini\Data\TextPart;
use Gemini\Enums\Role;
use Gemini\Data\Tool;
// use Gemini\Data\Schema;

class McpTaskController extends Controller
{
    //     // Gemini
    //     public function createTask(Request $request)
    //     {
    //         $talks = [];
    //         array_unshift(
    //             $talks,
    //             [
    //                 "role" => "model",
    //                 "message" => <<<PROMPT
    // あなたはタスク管理AIです。
    // ルール：
    // - ユーザーが「やりたい」「追加して」「課題にして」と言った場合は create_task を使う
    // - ユーザーが「説明して」「どういう意味？」と聞いた場合は tool を使わず文章で返す
    // - 曖昧な場合は質問で確認する
    // PROMPT
    //             ]
    //         );
    //         $histories = [];
    //         foreach ($talks as $talk) {
    //             Log::debug('talk------');
    //             Log::debug($talk);
    //             $histories[] = Content::parse(
    //                 part: $talk["message"],
    //                 // role: $service->formatSpeaker($talk["select_speaker"], "gemini"),
    //                 role: $talk["role"] === "user" ? Role::USER : Role::MODEL
    //                 // role: $talk["role"] === "user" ? Role::USER : Role::SYSTEM
    //             );
    //         }

    //         // gemini-2.5-flash
    //         $chat = Gemini::generativeModel(model: "gemini-2.5-flash")
    //             ->startChat(history: $histories);

    //         $message = '私はLaravelとTypescriptを使うバックエンドエンジニアです。基礎的なものから進めたいのですが、毎日達成できる課題を出してください。';
    //         $response = $chat->sendMessage($message);
    //         $text = $response->text();

    //         // ##################################
    //         // geminiのレスポンスを処理する
    //         // ##################################
    //         if ($message === null) return false;
    //         // Log::debug($response);
    //         return $text;
    //     }

    // 使わない
    // public function createTask(Request $request)
    // {
    //     $userMessage = $request->input('title');
    //     if (!$userMessage) {
    //         return response()->json([
    //             'error' => 'title is required'
    //         ], 400);
    //     }

    //     $createTaskFunction = new FunctionDeclaration(
    //         name: 'create_task',
    //         description: 'ユーザーの課題を作成する',
    //         parameters: new Schema(
    //             type: DataType::OBJECT,
    //             properties: [
    //                 'title' => new Schema(
    //                     type: DataType::STRING,
    //                     description: 'タスクのタイトル'
    //                 ),
    //             ],
    //             required: ['title']
    //         )
    //     );

    //     $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
    //         ->withTool(new Tool([$createTaskFunction]))
    //         // ->withTool(new Tool(googleSearch: GoogleSearch::from()))
    //         ->generateContent(
    //             '私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。基礎的なものから進めたいのですが、毎日達成できる課題を出してください。'
    //             // '毎日ストレッチする課題を作りたい'
    //         );

    //     // $response = Gemini::generativeModel(model: 'gemini-2.5-flash')
    //     //     ->generateContent(
    //     //         contents: $userMessage,
    //     //         tools: [$createTaskFunction]
    //     //     );
    //     // ->tools([$createTaskFunction])
    //     // // ->withFunctions([$createTaskFunction])
    //     // ->generateContent(
    //     //     '毎日ストレッチする課題を作りたい'
    //     // );
    //     $candidate = $response->candidates[0];
    //     $part = $candidate->content->parts[0];

    //     if ($part->functionCall) {
    //         $functionName = $part->functionCall->name;
    //         $args = $part->functionCall->args;
    //         if ($functionName === 'create_task') {
    //             $mcpResponse = Http::post(
    //                 'http://host.docker.internal:3333/tools/create_task',
    //                 $args
    //             );

    //             return response()->json([
    //                 'status' => 'task_created',
    //                 'task' => $mcpResponse->json(),
    //             ]);
    //         }
    //     }
    //     return response()->json([
    //         'message' => $part->text,
    //     ]);
    //     return response()->json([
    //         'message' => "end",
    //     ]);
    // }

    /** 
     * test
     */
    public function mcpTest(Request $request)
    {
        $title = $request->input('title');
        $userId = $request->input('userId');
        $category = $request->input('category');
        if (!$title) {
            return response()->json(['error' => 'title is required'], 400);
        }
        $response = Http::post(
            'http://host.docker.internal:3333/tools/create_task',
            [
                'title' => $title,
                'userId' => $userId,
                'category' => $category,
            ]
        );
        return response()->json([
            'mcp_response' => $response->json(),
        ]);
    }
}
