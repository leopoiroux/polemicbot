<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Coderjerk\BirdElephant\BirdElephant;
use Coderjerk\BirdElephant\Compose\Tweet;
use Coderjerk\BirdElephant\Compose\Reply;

// Only allowed for cli
if (PHP_SAPI !== 'cli') {
    die('Not allowed');
}

// Start Timer
$start = microtime(true);

// Load .env data
$dotenv = Dotenv::createUnsafeImmutable(__DIR__ . '/../config', '.env');
$dotenv->safeLoad();
$env = getenv();

$credentials = array(
    'consumer_key' => $env['CONSUMER_KEY'], // identifies your app, always needed
    'consumer_secret' => $env['CONSUMER_SECRET'], // app secret, always needed
    'bearer_token' => $env['BEARER_TOKEN'], // OAuth 2.0 Bearer Token requests
    'token_identifier' => $env['TOKEN_IDENTIFIER'], // OAuth 1.0a User Context requests
    'token_secret' => $env['TOKEN_SECRET'], // OAuth 1.0a User Context requests
);

$twitter = new BirdElephant($credentials);

// Load every mentions of @PolemicBot
$mentions = $twitter->user('polemicbot')->mentions();
if (!empty($mentions) && !empty($mentions->data)) {
    foreach ($mentions->data as $mention) {
        // Get conversation_id
        $tweet = $twitter->tweets()->get($mention->id, ['tweet.fields' => 'conversation_id']);
        if (!empty($tweet) && !empty($tweet->data) && !empty($tweet->data->conversation_id)) {
            // Get retweeters from conversation
            $retweeters = $twitter->tweets()->retweeters($tweet->data->conversation_id, ['user.fields' => 'public_metrics,location']);
            if (!empty($retweeters) && !empty($retweeters->data)) {
                $users = [];
                $biggest = 0;
                foreach ($retweeters->data as $user) {
                    if ($user->public_metrics->followers_count < 500) {
                        $users['trolls'][] = $user;
                    } else {
                        $users['interesting'][] = $user;
                    }

                    if ($user->public_metrics->followers_count > $biggest) {
                        $biggest = $user->public_metrics->followers_count;
                        $users['biggest'] = $user;
                    }
                }

                $quote = sprintf('%s trolls - %s interesting - @%s is the biggest with %s followers', is_countable($users['trolls']) ? count($users['trolls']) : 0, is_countable($users['interesting']) ? count($users['interesting']) : 0, $users['biggest']->username, $users['biggest']->public_metrics->followers_count);
                print_r($quote);
                // $reply = (new Reply())->inReplyToTweetId($mention->id);
                // $tweet = (new Tweet())->text($quote);
                // $twitter->tweets()->tweet($tweet);
                // $tweet = (new \Coderjerk\BirdElephant\Compose\Tweet())->text(".@coderjerk is so cool");
                // $twitter->tweets()->tweet($tweet);
            }
        }
    }
}

echo "\n" . 'Execution time ' . round(microtime(true) - $start, 2) . ' seconds';
