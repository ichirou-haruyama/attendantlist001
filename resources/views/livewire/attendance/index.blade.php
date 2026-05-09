<?php

use App\Models\AttendanceRecord;
use App\Models\Member;
use Carbon\CarbonImmutable;
use Livewire\Volt\Component;

new class extends Component {
    public string $selectedDate = '';
    public int $total = 0;
    public int $present = 0;
    public int $absent = 0;
    public int $unchecked = 0;

    public function mount(): void
    {
        $date = request()->query('date');

        if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1) {
            $selected = CarbonImmutable::createFromFormat('Y-m-d', $date);
            $this->selectedDate = $selected->format('Y-m-d');
        } else {
            $today = CarbonImmutable::today();
            $this->selectedDate = $today->format('Y-m-d');
        }

        $this->refreshSummary();
    }

    /**
     * 一覧データを取得します（毎回最新状態を表示）。
     */
    public function members()
    {
        $members = Member::query()->orderBy('id')->get();
        $records = AttendanceRecord::query()
            ->whereDate('attendance_date', $this->selectedDate)
            ->get()
            ->keyBy('member_id');

        return $members->map(function (Member $member) use ($records) {
            $member->daily_status = $records[$member->id]->status ?? Member::STATUS_UNCHECKED;

            return $member;
        });
    }

    /**
     * 出欠集計を再計算します。
     */
    public function refreshSummary(): void
    {
        $this->total = Member::query()->count();
        $this->present = AttendanceRecord::query()
            ->whereDate('attendance_date', $this->selectedDate)
            ->where('status', Member::STATUS_PRESENT)
            ->count();
        $this->absent = AttendanceRecord::query()
            ->whereDate('attendance_date', $this->selectedDate)
            ->where('status', Member::STATUS_ABSENT)
            ->count();
        $this->unchecked = max($this->total - $this->present - $this->absent, 0);
    }

    /**
     * メンバーの状態を更新し、画面を即時反映します。
     */
    public function markStatus(int $memberId, string $status): void
    {
        if (! in_array($status, [Member::STATUS_PRESENT, Member::STATUS_ABSENT], true)) {
            return;
        }

        AttendanceRecord::query()->updateOrCreate(
            [
                'member_id' => $memberId,
                'attendance_date' => $this->selectedDate,
            ],
            [
                'status' => $status,
            ],
        );

        $this->refreshSummary();
    }

    /**
     * 出欠状態を全員「未確認」に戻します。
     */
    public function resetStatuses(): void
    {
        AttendanceRecord::query()
            ->whereDate('attendance_date', $this->selectedDate)
            ->delete();
        $this->refreshSummary();
    }
}; ?>

<div class="min-h-screen bg-slate-100 py-4">
    <div class="mx-auto w-full max-w-3xl px-4">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-800">出席確認表</h1>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('attendance.calendar') }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    カレンダーへ
                </a>
                <a
                    href="{{ route('attendance.admin') }}"
                    class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white"
                >
                    管理画面へ
                </a>
            </div>
        </div>

        <div class="mb-4 rounded-xl bg-white p-4 shadow-sm">
            <p class="text-sm font-semibold text-slate-700">対象日: {{ \Carbon\CarbonImmutable::parse($selectedDate)->format('Y年n月j日') }}</p>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">登録人数</p>
                <p class="text-xl font-bold text-slate-800">{{ $total }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">出席人数</p>
                <p class="text-xl font-bold text-emerald-600">{{ $present }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">欠席人数</p>
                <p class="text-xl font-bold text-rose-600">{{ $absent }}</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-500">未確認人数</p>
                <p class="text-xl font-bold text-slate-500">{{ $unchecked }}</p>
            </div>
        </div>

        <div class="space-y-3">
            @foreach ($this->members() as $member)
                <article class="rounded-xl bg-white p-4 shadow-sm">
                    <p class="text-lg font-semibold text-slate-900">{{ $member->name }}</p>
                    <p class="mb-2 text-sm text-slate-500">{{ $member->company ?? '所属未設定' }}</p>

                    <p class="mb-3 text-sm text-slate-500">状態：</p>
                    <x-status-badge :status="$member->daily_status" class="mb-3" />

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            wire:click="markStatus({{ $member->id }}, '{{ \App\Models\Member::STATUS_PRESENT }}')"
                            class="rounded-lg bg-emerald-500 px-4 py-3 text-base font-bold text-white active:scale-[0.99]"
                        >
                            出
                        </button>
                        <button
                            type="button"
                            wire:click="markStatus({{ $member->id }}, '{{ \App\Models\Member::STATUS_ABSENT }}')"
                            class="rounded-lg bg-rose-500 px-4 py-3 text-base font-bold text-white active:scale-[0.99]"
                        >
                            欠
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button
                type="button"
                wire:click="resetStatuses"
                wire:confirm="このページの対象日（{{ \Carbon\CarbonImmutable::parse($selectedDate)->format('Y年n月j日') }}）の出欠状態をすべて未確認に戻します。よろしいですか？"
                class="rounded-lg bg-orange-500 px-5 py-3 text-base font-bold text-white shadow-sm active:scale-[0.99]"
            >
                出欠をリセット
            </button>
        </div>
    </div>
</div>
