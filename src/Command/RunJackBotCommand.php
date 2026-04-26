<?php

namespace Waeris\JackBot\Command;

use Flarum\Post\CommentPost;
use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RunJackBotCommand extends Command
{
    protected $signature = 'jackbot:run {postId} {discussionId}';
    protected $description = 'Запуск ИИ Капитана Джека для ответа на пост';

    public function handle(SettingsRepositoryInterface $settings)
    {
        $postId = $this->argument('postId');
        $discussionId = $this->argument('discussionId');
        
        // Берем настройки из админки
        $delay = (int) $settings->get('waeris-jackbot.delay', 35);
        $apiKey = $settings->get('waeris-jackbot.api_key');
        $prompt = $settings->get('waeris-jackbot.system_prompt');
        $botId = (int) $settings->get('waeris-jackbot.user_id', 6);

        // Имитируем "обдумывание"
        sleep($delay);

        // Ищем пост, на который нужно ответить
        $parentPost = \Flarum\Post\Post::find($postId);
        if (!$parentPost) return;

        // Чистим текст от упоминания @Jack
        $userText = preg_replace('/@"Jack"#\d+\s*/i', '', $parentPost->content);
        $userText = trim(strip_tags($userText));

        // Запрос к Groq API
        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' => $userText]
            ],
            'max_tokens' => 1024
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $result = json_decode($response, true);
        $replyText = $result['choices'][0]['message']['content'] ?? null;

        // Если ИИ ответил — создаем пост в Flarum
        if ($replyText) {
            $post = new CommentPost();
            $post->discussion_id = $discussionId;
            $post->user_id = $botId;
            $post->content = $replyText;
            $post->created_at = Carbon::now();
            $post->save();

            // Обновляем данные дискуссии (последний пост, счетчик)
            $discussion = Discussion::find($discussionId);
            $discussion->last_post_id = $post->id;
            $discussion->last_posted_at = $post->created_at;
            $discussion->last_posted_user_id = $botId;
            $discussion->comment_count++;
            $discussion->save();
        }
    }
}
