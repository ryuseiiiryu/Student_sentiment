<?php

class SentimentService
{
    private array $positiveWords = [
        'love','like','enjoy','enjoyed','good','great','amazing','helpful','clear','fun','exciting',
        'excellent','nice','understand','understood','learning','learned','organized','interesting',
        'happy','satisfied','motivated','engaging','improve','improved','best','wonderful','effective', 
        'awesome','well','easy','smooth','positive','fantastic','brilliant','fascinating','informative'
    ];

    private array $negativeWords = [
        'hate','dislike','boring','confusing','difficult','hard','terrible','bad','worst','useless',
        'stressful','confused','tired','annoying','frustrating','slow','noisy','poor','unclear',
        'messy','rushed','badly','not clear','did not understand','don\'t understand','worse','problem'
    ];

    public function __construct() {}

    public function classifyText(string $text): string
    {
        $text = mb_strtolower($text);
        $tokens = preg_split('/[^a-z]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $pos = 0;
        $neg = 0;

        foreach ($tokens as $token) {
            if (in_array($token, $this->positiveWords, true)) {
                $pos++;
            }
            if (in_array($token, $this->negativeWords, true)) {
                $neg++;
            }
        }

        foreach ($this->negativeWords as $phrase) {
            if (str_contains($phrase, ' ') && str_contains($text, $phrase)) {
                $neg++;
            }
        }

        if ($pos > $neg) {
            return 'positive';
        }

        if ($neg > $pos) {
            return 'negative';
        }

        return 'neutral';
    }
}
