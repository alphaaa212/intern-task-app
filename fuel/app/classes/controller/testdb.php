<?php

class Controller_Testdb extends Controller
{
    public function action_index()
    {
        try {
            // DBに接続して現在時刻を取得
            $result = DB::query("SELECT NOW() AS now_time")->execute()->current();

            return "✅ DB接続成功！現在時刻: " . $result['now_time'];
        } catch (Exception $e) {
            return "❌ DB接続失敗: " . $e->getMessage();
        }
    }
}