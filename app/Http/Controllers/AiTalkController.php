<?php

namespace App\Http\Controllers;

use App\Models\AiTalk;
use App\Models\AiTalkSession;
use App\Services\AiTalkService;
use App\Services\AiTalkHistoryService;
use App\Services\AiTalkSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Gemini\Laravel\Facades\Gemini;

class AiTalkController extends Controller
{
  // Gemini
  public function geminiGenerateTalk(Request $request, AiTalkService $aiTalkService, AiTalkSessionService $aiTalkSessionService, AiTalkHistoryService $service)
  {
    // $talkHistory = AiTalkHistory::all();
    $userId = auth()->id();
    $taskId = 6;
    // $taskId = $request["task_id"];

    Log::debug("aiAgent select");
    Log::debug(new Carbon('now'));
    $aiAgent = $aiTalkService->getAgent($userId);
    $aiSession = $aiTalkSessionService->get($userId, $aiAgent->id, $request->session_id ?? null);
    // Log::debug($aiAgent);
    // Log::debug($aiSession);
    if ($aiSession->count() === 0) {
      // sessionが初めてであれば、新しく開始する
      $aiSession = $aiTalkSessionService->create($userId, $aiAgent->id);
      if (!$aiSession) {
        return ["status" => 501, "message" =>  "Session開始に失敗しました。"];
      }
    }
    $aiAgentId = $aiAgent->id;
    $aiAgentName = $aiAgent->name;
    $aiSessionId = $aiSession->id;
    Log::debug("aiAgent select end");
    Log::debug(new Carbon('now'));

    // 会話履歴を取得
    Log::debug("talkHistorys search");
    Log::debug(new Carbon('now'));
    $talkHistorys = $service->get($userId, $aiAgentId, $aiSessionId, $taskId);
    Log::debug($talkHistorys);
    // AI使用制限を確認
    if ($talkHistorys->count() !== 0) {
      // Log::debug($talkHistorys);
      // AITalk履歴がある際に、使用可能な状態か判定する
      if ($talkHistorys[0]["geminiIsActive"] === 0) {
        return ["status" => 401, "message" =>  "Ai認証エラー"];
      }
      // 会話で使用するAIAgentをセット
      $aiAgentName = $talkHistorys[0]["geminiName"];
    }
    Log::debug("talkHistorys search end");
    Log::debug(new Carbon('now'));

    // defalut data
    Log::debug("default data set");

    // TODO
    // タスク生成時にセッションが同じ時にカテゴリーを追加しないように制御する必要がある

    $talks = [];
    $today = now();
    array_unshift(
      $talks,
      [
        "role" => "model",
        "message" => <<<PROMPT
あなたは明るく優しいサポートキャラ「葵」です。
通常会話は必ず 50〜100文字以内で返してください。

PROMPT
      ],
      [
        "role" => "model",
        "message" => <<<PROMPT
あなたはタスク生成アシスタントです。

これからユーザーと会話を行った上で、
会話内容に対する自然な返答と、
カテゴリー + タスク一覧のJSONを同時に出力してください。

以下の出力フォーマットを絶対に守ってください：

<CONVERSATION>
ここにユーザーとの会話に対する自然な返答を書く。
Markdown装飾やコードブロック（```）は使わないこと。
</CONVERSATION>

<JSON_OUTPUT>
ここにカテゴリーとタスクのJSONデータを出力する。

以下のJSON形式を厳守すること：
{"category": {"name": "string","description": "string"},"tasks": [{"name": "string","description": "string","start_date": "YYYY-MM-DD HH:MM:SS","end_date": "YYYY-MM-DD HH:MM:SS","difficulty": "easy | medium | hard"}]}

JSONの前後に余計な文章、コードブロック、記号、改行は入れないこと。
</JSON_OUTPUT>

重要事項：
- <CONVERSATION> と </CONVERSATION> のタグは絶対に消さない・壊さないこと。
- <JSON_OUTPUT> と </JSON_OUTPUT> のタグは絶対に消さない・壊さないこと。
- JSON は必ず有効な構文にすること。
- JSONは必ず1つだけ出力すること。
- JSON部分にはMarkdown装飾を入れないこと。
- 文章部分では自由に返信してよいが、コードブロックは禁止。

● ルール
- タスクは3〜7件。
- start_date/end_date は必ず {$today}〜{$today->addWeekday()} の範囲。
- 日付がある場合、今日から2週間以内にする。
- 通常会話なしで JSON だけ返すのは禁止。
- JSON は有効な構造で返す。
PROMPT
      ],
    );

    // 過去の会話データを会話履歴に含める
    Log::debug("talkHistorys set start");
    if ($talkHistorys->count() !== 0) {
      foreach ($talkHistorys as $talkHistory) {
        Log::debug("talkHistorys old set");
        $role = $service->formatSpeaker($talkHistory["select_speaker"], "gemini");
        array_push($talks, ["role" => $role, "message" => $talkHistory["message"]]);
        // $lastMessage = $talkHistorys ? data_get($talkHistorys, (count($talkHistorys) - 1) . '.message', '') : "";
      }
    } else if ($talkHistorys->count() == 0) {
      Log::debug("talkHistorys new set");
      $conversationSummary = "ユーザーの目標を最初に質問してください。ユーザーの回答を待ってからタスクの作成をしてください。";
      array_push($talks, ["role" => "model", "message" => $conversationSummary]);
      // array_push($talks, ["role" => Role::SYSTEM, "part" => $conversationSummary]);
    }

    // template
    $histories = [];
    foreach ($talks as $talk) {
      Log::debug('talk------');
      Log::debug($talk);
      $histories[] = Content::parse(
        part: $talk["message"],
        // role: $service->formatSpeaker($talk["select_speaker"], "gemini"),
        role: $talk["role"] === "user" ? Role::USER : Role::MODEL
        // role: $talk["role"] === "user" ? Role::USER : Role::SYSTEM
      );
    }
    Log::debug("histories");

    // $response = Gemini::generativeModel(model: $talkHistorys[0]["geminiName"])
    //   ->startChat(history: $histories);

    return $histories;

    // gemini-2.5-flash
    Log::debug($aiAgentName);
    $chat = Gemini::generativeModel(model: $aiAgentName)
      ->startChat(history: $histories);
    Log::debug("gemini set Model");

    // Userからの会話を入れる
    Log::debug("gemini sendMessage");
    // $message = $request->message;
    $message = '私はLaravelとTypescriptを使うバックエンドエンジニアです。技術力が低いので勉強したいです。基礎的なものから進めたいのですが、毎日達成できる課題を出してください。';
    $response = $chat->sendMessage($message);
    $text = $response->text();
    // return response()->json([
    //   'text' => $text,
    // ]);


    // ##################################
    // geminiのレスポンスを処理する
    // ##################################
    // $responseJson = Storage::get('response.json');
    // $response = json_decode($responseJson, true);
    // $text = $response['text'] ?? null;
    if ($text === null) {
      throw new \Exception('text がないよ！');
      return false;
    }
    Log::debug("text");
    Log::debug($text);
    // ユーザーとの会話データ（CONVERSATION 抜き出し）
    preg_match('/<CONVERSATION>\s*(.*?)\s*<\/CONVERSATION>/s', $text, $conversationMatches);
    if (!isset($conversationMatches[1])) {
      throw new \Exception("CONVERSATION が見つからないよ！");
      return false;
    }
    $conversation = trim($conversationMatches[1]);
    if ($conversation === null) {
      Log::debug("gemini not create talk");
      return false;
    }
    // 会話履歴を保存する
    // Log::debug($conversation);
    Log::debug("AiTalkHistoryService saveMessage");
    $talkHistoryService = new AiTalkHistoryService();
    // $message, $userId, $aiAgentId, $flg
    $talkHistoryService->saveMessage($message, $userId, $aiAgentId, $aiSessionId, Role::USER);
    $talkHistoryService->saveMessage($conversation, $userId, $aiAgentId, $aiSessionId, Role::MODEL);

    // カテゴリーとタスクデータ（JSON_OUTPUT を抜き出す）
    preg_match('/<JSON_OUTPUT>\s*(\{.*\})\s*<\/JSON_OUTPUT>/s', $text, $matches);
    if (!isset($matches[1])) {
      throw new \Exception("JSON_OUTPUT が見つからないよ！");
      return false;
    }
    // ここは encode しない！！
    $taskData = json_decode($matches[1], true);
    if ($taskData === null) {
      Log::debug("gemini not create category & tasks");
      return false;
    }
    // return $taskData["category"];
    // return $taskData["tasks"];

    Log::debug("AiTalkService formatAiTalkData");
    $taskService = new AiTalkService();
    $result = $taskService->formatAiTalkData($taskData, $userId);
    return $result;
  }
}
