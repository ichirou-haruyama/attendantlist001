<?php

use App\Models\Member;
use Livewire\Volt\Component;

new class extends Component {
    public int $total = 0;
    public int $present = 0;
    public int $absent = 0;
    public int $unchecked = 0;

    public function mount(): void
    {
        $this->refreshSummary();
    }

    /**
     * 一覧データを取得します（毎回最新状態を表示）。
     */
    public function members()
    {
        return Member::query()->orderBy('id')->get();
    }

    /**
     * 出欠集計を再計算します。
     */
    public function refreshSummary(): void
    {
        $this->total = Member::query()->count();
        $this->present = Member::query()->where('status', Member::STATUS_PRESENT)->count();
        $this->absent = Member::query()->where('status', Member::STATUS_ABSENT)->count();
        $this->unchecked = Member::query()->where('status', Member::STATUS_UNCHECKED)->count();
    }

    /**
     * メンバーの状態を更新し、画面を即時反映します。
     */
    public function markStatus(int $memberId, string $status): void
    {
        if (! in_array($status, [Member::STATUS_PRESENT, Member::STATUS_ABSENT], true)) {
            return;
        }

        Member::query()->whereKey($memberId)->update(['status' => $status]);
        $this->refreshSummary();
    }

    /**
     * 出欠状態を全員「未確認」に戻します。
     */
    public function resetStatuses(): void
    {
        Member::query()->update(['status' => Member::STATUS_UNCHECKED]);
        $this->refreshSummary();
    }
}; ?>

<div class="min-h-screen bg-slate-100 py-4">
    <div class="mx-auto w-full max-w-3xl px-4">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-800">出席確認表</h1>
            <a
                href="{{ route('attendance.admin') }}"
                class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white"
            >
                管理画面へ
            </a>
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
                    <x-status-badge :status="$member->status_label" class="mb-3" />

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
                wire:confirm="出欠状態をすべて未確認に戻します。よろしいですか？"
                class="rounded-lg bg-orange-500 px-5 py-3 text-base font-bold text-white shadow-sm active:scale-[0.99]"
            >
                出欠をリセット
            </button>
        </div>
    </div>
</div>
