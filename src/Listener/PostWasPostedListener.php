<?php

namespace Waeris\JackBot\Listener;

use Flarum\Post\Event\Posted;
use Flarum\Settings\SettingsRepositoryInterface;

class PostWasPostedListener
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(Posted $event)
    {
        $post = $event->post;
        $discussion = $post->discussion;

        // Проверяем, включен ли бот в админке
        if (!$this->settings->get('waeris-jackbot.enabled')) return;

        // Чтобы бот не отвечал сам себе (проверка по ID)
        $botId = (int) $this->settings->get('waeris-jackbot.user_id');
        if ($post->user_id === $botId) return;

        // Проверка разрешенных тегов
        $allowedTags = explode(',', (string) $this->settings->get('waeris-jackbot.allowed_tags'));
        $discussionTags = $discussion->tags->pluck('slug')->toArray();
        if (!empty($allowedTags[0]) && !array_intersect($allowedTags, $discussionTags)) return;

        // Ищем упоминание @Jack
        if (stripos($post->content, '@Jack') === false && stripos($post->content, '@"Jack"') === false) return;

        // Запуск фонового процесса (команды)
        $phpPath = '/opt/alt/php84/usr/bin/php';
        $flarumPath = base_path('flarum');
        
        $command = "{$phpPath} {$flarumPath} jackbot:run {$post->id} {$discussion->id} > /dev/null 2>&1 &";
        shell_exec($command);
    }
}
