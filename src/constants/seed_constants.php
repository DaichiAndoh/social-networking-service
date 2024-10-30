<?php

/**
 * init
 *  `seed --init` で実行されるデータシーディングの各種挿入レコード数
 */
// インフルエンサー関連
const INIT_INFLUENCER_COUNT = 50;
const INIT_INFLUENCER_POST_MIN_COUNT = 10;
const INIT_INFLUENCER_POST_MAX_COUNT = 30;
const INIT_INFLUENCER_REPLY_MIN_COUNT = 5;
const INIT_INFLUENCER_REPLY_MAX_COUNT = 10;
const INIT_INFLUENCER_FOLLOW_MIN_COUNT = 3;
const INIT_INFLUENCER_FOLLOW_MAX_COUNT = 10;

// ユーザー関連
const INIT_GUEST_COUNT = 10;
const INIT_USER_COUNT = 100;
const INIT_USER_POST_MIN_COUNT = 10;
const INIT_USER_POST_MAX_COUNT = 30;
const INIT_USER_REPLY_MIN_COUNT = 5;
const INIT_USER_REPLY_MAX_COUNT = 10;
const INIT_USER_FOLLOW_MIN_COUNT = 10;
const INIT_USER_FOLLOW_MAX_COUNT = 30;

// インフルエンサー, ユーザー共通（いいね）
const INIT_LIKE_TO_INFLUENCER_COUNT = 20;
const INIT_LIKE_TO_USER_COUNT = 5;


/**
 * batch
 *  `seed --batch` で実行されるデータシーディングの各種挿入レコード数
 */
// バッチプログラム実行時間
const BATCH_HOURS = [7, 8, 12, 13, 17, 18, 19, 20, 21, 22];

// インフルエンサー関連
const BATCH_INFLUENCER_POST_COUNT = 3;

// ユーザー関連
const BATCH_USER_POST_COUNT = 3;
const BATCH_USER_REPLY_COUNT = 1;
const BATCH_USER_LIKE_TO_INFLUENCER_COUNT = 20;
const BATCH_USER_LIKE_TO_USER_COUNT = 5;
