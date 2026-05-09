<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>出席確認表</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f6f8;
            color: #1f2937;
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
            padding: 16px;
        }

        .panel {
            background: #ffffff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
            margin-bottom: 12px;
        }

        h1 {
            font-size: 1.4rem;
            margin: 0 0 12px;
        }

        .summary {
            display: grid;
            gap: 6px;
            font-size: 0.95rem;
        }

        .member-list {
            display: grid;
            gap: 10px;
        }

        .member-card {
            border: 1px solid #dbe3ea;
            border-radius: 10px;
            padding: 12px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 0.95rem;
        }

        .status {
            font-weight: 700;
        }

        .status-present { color: #0f766e; }
        .status-absent { color: #b91c1c; }
        .status-unconfirmed { color: #6b7280; }

        .actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }

        button {
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-present {
            background: #14b8a6;
            color: #ffffff;
        }

        .btn-absent {
            background: #ef4444;
            color: #ffffff;
        }
    </style>
</head>
<body>
<main class="container">
    <section class="panel">
        <h1>出席確認表</h1>
        <div class="summary">
            <div>登録人数：{{ $counts['total'] }}名</div>
            <div>出席：{{ $counts['present'] }}名　欠席：{{ $counts['absent'] }}名　未確認：{{ $counts['unconfirmed'] }}名</div>
        </div>
    </section>

    <section class="member-list">
        @forelse ($members as $member)
            @php
                $statusClass = match ($member->attendance_status) {
                    \App\Models\Member::STATUS_PRESENT => 'status-present',
                    \App\Models\Member::STATUS_ABSENT => 'status-absent',
                    default => 'status-unconfirmed',
                };
            @endphp
            <article class="panel member-card">
                <div class="row"><span>氏名</span><span>{{ $member->name }}</span></div>
                <div class="row"><span>所属</span><span>{{ $member->organization }}</span></div>
                <div class="row"><span>状態</span><span class="status {{ $statusClass }}">{{ $member->attendance_status_label }}</span></div>

                <div class="actions">
                    <form action="{{ route('attendance.update-status', $member) }}" method="POST">
                        @csrf
                        <input type="hidden" name="attendance_status" value="{{ \App\Models\Member::STATUS_PRESENT }}">
                        <button class="btn-present" type="submit">出</button>
                    </form>
                    <form action="{{ route('attendance.update-status', $member) }}" method="POST">
                        @csrf
                        <input type="hidden" name="attendance_status" value="{{ \App\Models\Member::STATUS_ABSENT }}">
                        <button class="btn-absent" type="submit">欠</button>
                    </form>
                </div>
            </article>
        @empty
            <section class="panel">
                表示できるメンバーがいません。まずはメンバーを登録してください。
            </section>
        @endforelse
    </section>
</main>
</body>
</html>
