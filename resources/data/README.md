
# 📘 Emotion Unlock Rules Format Specification

## 🔖 概要

このJSONファイルは、アプリ内の感情解禁ルールを管理するための設定ファイルです。  
投稿数や特定感情の使用履歴などを条件に「新しい感情」を解禁し、継続利用を促すためのゲーム性を実現します。

---

## 🗂 JSONフォーマット例

```jsonc
[
  {
    "emotion": "confused",
    "unlockType": "combo",
    "conditions": [
      { "type": "post_count", "threshold": 25 },
      { "type": "unlocked_emotions", "emotions": ["anxious", "frustrated"] }
    ],
    "isSecret": true,
    "hint": "不安と怒りが混ざり、ある程度投稿すると現れる"
  }
]
```

---

## 🔍 各フィールドの説明

| フィールド名     | 型       | 必須 | 説明 |
|------------------|----------|------|------|
| `emotion`        | string   | ✅   | 対象感情（`EmotionState` enum に対応） |
| `unlockType`     | string   | ✅   | 解禁方法：`initial`, `post_count`, `base_emotion`, `combo` のいずれか |
| `threshold`      | number   | ❌   | `post_count` や `base_emotion` 条件で使用される閾値 |
| `baseEmotion`    | string   | ❌   | `base_emotion` 条件で参照する感情 |
| `conditions`     | array    | ❌   | `combo` タイプの複数条件（下記参照） |
| `isSecret`       | boolean  | ❌   | UIで非表示にする隠し感情かどうか |
| `hint`           | string   | ❌   | シークレット用のヒントテキスト（UI表示用） |

---

## 📎 `conditions`（combo条件）内で使えるタイプ

| `type`              | 詳細説明 |
|---------------------|----------|
| `"post_count"`       | 投稿数がしきい値（`threshold`）以上かどうか |
| `"unlocked_emotions"`| 指定した感情群がすでに解禁されているか |
| `"base_emotion"`     | 特定の感情が何回投稿されているか（感情分析による） |

---

## 📁 推奨配置場所

```
resources/data/emotion_unlock_rules.json
resources/data/README.md
```

読み込み例（Laravel）:

```php
use Illuminate\Support\Facades\File;

$rules = json_decode(
    File::get(resource_path('data/emotion_unlock_rules.json')),
    true
);
```

---

## ✍️ 拡張時の指針

- `UnlockEvaluator` クラスにて新しい `unlockType` や `conditions.type` を評価できるように実装を追加してください。
- `isSecret` + `hint` を活用すれば、UI上で「ヒントはあるが正体不明な感情」を表示できます。
- 今後、ランダム性や期間限定イベント感情などの導入も視野に入れる場合、このファイルを中心に管理するのが柔軟です。

---
