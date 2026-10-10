<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chat Grace Period
    |--------------------------------------------------------------------------
    |
    | Berapa jam chat masih bisa dikirim setelah order berstatus 'completed'.
    | Setelah lewat batas ini, chat jadi read-only.
    |
    */
    'chat_grace_hours' => env('ORDER_CHAT_GRACE_HOURS', 24),
];