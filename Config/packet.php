<?php

return [
    'list' => [
        ['id' => 1, 'name' => '1元', 'money' => 1, 'score' => 1000],
        ['id' => 2, 'name' => '2元', 'money' => 2, 'score' => 2000],
        ['id' => 3, 'name' => '3元', 'money' => 3, 'score' => 3000],
        ['id' => 4, 'name' => '5元', 'money' => 5, 'score' => 5000],
        ['id' => 5, 'name' => '10元', 'money' => 10, 'score' => 10000],
        ['id' => 6, 'name' => '30元', 'money' => 30, 'score' => 30000],
    ],
    'poundage' => 200, // 积分
    'restrict' => [
        'user_per_day' => 100, //元
        'cash_pooling_per_day' => 10000, // 元
    ],
];