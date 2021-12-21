<?php

return [
    'kms' => [
        'en' => [
            'id' => env('FB_KMS_EN_USER_ID'),
            'access_token' => env('FB_KMS_EN_PAGE_ACCESS_TOKEN'),
            'comment_table_id' => 'facebook_comments_kms_en',
            'risk_word_table_id' => 'facebook_risk_comments',
        ],
    ],
    'argo' => [
        'en' => [
            'id' => env('FB_ARGO_EN_USER_ID'),
            'access_token' => env('FB_ARGO_EN_PAGE_ACCESS_TOKEN'),
            'comment_table_id' => 'facebook_comments_argo_en',
            'risk_word_table_id' => 'facebook_risk_comments',
        ],
    ],
];
