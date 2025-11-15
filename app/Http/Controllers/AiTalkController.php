<?php

namespace App\Http\Controllers;

use App\Models\AiTalk;
use Illuminate\Support\Facades\Http;

class AiTalkController extends Controller
{
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
    public function generateTalk(AiTalk $aiTalk)
    {
        $talkHistory = AiTalk::all();
        return $talkHistory;
        // $talkHistory = [
        //     ["role" => "system", "content" => "あなたは明るくて優しい勉強サポートキャラ『葵』です。５０文字以内で返してください。"],
        //     ["role" => "user", "content" => "私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。毎日達成できる課題を出してください。"]
        // ];
        // $response = Http::timeout(60)->post('http://ollama:11434/api/chat', [
        //     'model' => 'aoi',
        //     'messages' => $talkHistory,
        //     'stream' => false,
        // ]);
        // if ($response["done"] === "true" && $response["message"]) {
        // }
        // return $response;
    }
}
