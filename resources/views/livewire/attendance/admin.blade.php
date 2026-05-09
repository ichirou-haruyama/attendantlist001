<?php

use App\Models\Member;
use Livewire\Volt\Component;

new class extends Component {
    public string $name = '';
    public string $company = '';

    public function members()
    {
        return Member::query()->orderBy('id')->get();
    }

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
        ]);

        Member::query()->create([
            'name' => $validated['name'],
            'company' => $validated['company'] === '' ? null : $validated['company'],
            'status' => Member::STATUS_UNCHECKED,
        ]);

        $this->reset('name', 'company');
    }

    public function deleteMember(int $memberId): void
    {
        Member::query()->whereKey($memberId)->delete();
    }
}; ?>

<div class="min-h-screen bg-slate-100 py-4">
    <div class="mx-auto w-full max-w-3xl px-4">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-800">出欠名簿管理</h1>
            <a
                href="{{ route('attendance.index') }}"
                class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white"
            >
                出欠確認表へ
            </a>
        </div>

        <section class="mb-4 rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">メンバー登録</h2>
            <form wire:submit="register" class="grid gap-3">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">氏名</label>
                    <input
                        id="name"
                        type="text"
                        wire:model="name"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                    >
                    @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="company" class="mb-1 block text-sm font-medium text-slate-700">所属</label>
                    <input
                        id="company"
                        type="text"
                        wire:model="company"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2"
                    >
                    @error('company') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <button
                    type="submit"
                    class="w-fit rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    登録する
                </button>
            </form>
        </section>

        <section class="rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">登録済みメンバー</h2>
            <div class="space-y-2">
                @forelse ($this->members() as $member)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                            <p class="text-sm text-slate-500">{{ $member->company ?? '所属未設定' }}</p>
                        </div>
                        <button
                            type="button"
                            wire:click="deleteMember({{ $member->id }})"
                            wire:confirm="本当に削除しますか？"
                            class="rounded-lg bg-rose-500 px-3 py-2 text-sm font-semibold text-white"
                        >
                            削除
                        </button>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">メンバーが登録されていません。</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
