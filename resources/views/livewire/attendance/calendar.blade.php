<?php

use App\Models\AttendanceRecord;
use App\Models\Member;
use Carbon\CarbonImmutable;
use Livewire\Volt\Component;

new class extends Component {
    public string $currentMonth = '';
    public string $selectedDate = '';
    public string $pendingDate = '';
    public bool $showDateActionChooser = false;

    public function mount(): void
    {
        $today = CarbonImmutable::today();
        $this->currentMonth = $today->format('Y-m');
        $this->selectedDate = $today->format('Y-m-d');
    }

    public function monthOptions(): array
    {
        $base = CarbonImmutable::today()->startOfMonth();
        $options = [];

        for ($i = -12; $i <= 12; $i++) {
            $month = $base->addMonths($i);
            $value = $month->format('Y-m');
            $options[] = [
                'value' => $value,
                'label' => $month->format('Y年n月'),
            ];
        }

        return $options;
    }

    public function updatedCurrentMonth(string $value): void
    {
        $month = CarbonImmutable::createFromFormat('Y-m', $value)->startOfMonth();
        $selected = CarbonImmutable::parse($this->selectedDate);

        if (! $selected->isSameMonth($month)) {
            $this->selectedDate = $month->format('Y-m-d');
        }
    }

    public function dayOptions(): array
    {
        $month = CarbonImmutable::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();
        $daysInMonth = $month->daysInMonth;
        $options = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->day($day);
            $options[] = [
                'value' => $date->format('Y-m-d'),
                'label' => $date->format('j日'),
            ];
        }

        return $options;
    }

    public function calendarCells(): array
    {
        $month = CarbonImmutable::createFromFormat('Y-m', $this->currentMonth)->startOfMonth();
        $startOffset = $month->dayOfWeek;
        $daysInMonth = $month->daysInMonth;
        $cells = array_fill(0, $startOffset, null);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $month->day($day);
            $cells[] = $date->format('Y-m-d');
        }

        return $cells;
    }

    public function chooseDate(string $date): void
    {
        $this->pendingDate = $date;
        $this->showDateActionChooser = true;
    }

    public function viewListForPendingDate(): void
    {
        if ($this->pendingDate === '') {
            return;
        }

        $this->selectedDate = $this->pendingDate;
        $this->currentMonth = CarbonImmutable::parse($this->pendingDate)->format('Y-m');
        $this->showDateActionChooser = false;
    }

    public function goToAttendanceSheet(): void
    {
        if ($this->pendingDate === '') {
            return;
        }

        $this->selectedDate = $this->pendingDate;
        $this->currentMonth = CarbonImmutable::parse($this->pendingDate)->format('Y-m');
        $this->showDateActionChooser = false;
        $this->redirectRoute('attendance.index', ['date' => $this->pendingDate], navigate: true);
    }

    public function cancelDateAction(): void
    {
        $this->showDateActionChooser = false;
        $this->pendingDate = '';
    }

    public function dailyMembers()
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
    }
}; ?>

<div class="min-h-screen bg-slate-100 py-4">
    <div class="mx-auto w-full max-w-5xl px-4">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold text-slate-800">出欠カレンダー管理</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('attendance.admin') }}" class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white">
                    出欠名簿管理へ
                </a>
                <a href="{{ route('attendance.index') }}" class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white">
                    出欠確認表へ
                </a>
            </div>
        </div>

        <section class="mb-4 rounded-xl bg-white p-4 shadow-sm">
            <div class="mb-4 grid gap-3 md:grid-cols-2">
                <div>
                    <label for="month" class="mb-1 block text-sm font-medium text-slate-700">月を選択</label>
                    <select id="month" wire:model.live="currentMonth" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach ($this->monthOptions() as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="day" class="mb-1 block text-sm font-medium text-slate-700">日付を選択</label>
                    <select id="day" wire:model.live="selectedDate" class="w-full rounded-lg border border-slate-300 px-3 py-2">
                        @foreach ($this->dayOptions() as $day)
                            <option value="{{ $day['value'] }}">{{ $day['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-7 gap-2 text-center text-xs font-semibold text-slate-500">
                @foreach (['日', '月', '火', '水', '木', '金', '土'] as $w)
                    <div>{{ $w }}</div>
                @endforeach
            </div>
            <div class="mt-2 grid grid-cols-7 gap-2">
                @foreach ($this->calendarCells() as $cell)
                    @if ($cell === null)
                        <div class="h-12 rounded-lg bg-slate-50"></div>
                    @else
                        <button
                            type="button"
                            wire:click="chooseDate('{{ $cell }}')"
                            class="h-12 rounded-lg border text-sm font-semibold {{ $selectedDate === $cell ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-slate-200 bg-white text-slate-700' }}"
                        >
                            {{ \Carbon\CarbonImmutable::parse($cell)->day }}
                        </button>
                    @endif
                @endforeach
            </div>

            @if ($showDateActionChooser && $pendingDate !== '')
                <div class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 p-3">
                    <p class="text-sm font-semibold text-indigo-800">
                        {{ \Carbon\CarbonImmutable::parse($pendingDate)->format('Y年n月j日') }} を選択しました
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="goToAttendanceSheet"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            出欠確認表へ遷移
                        </button>
                        <button
                            type="button"
                            wire:click="viewListForPendingDate"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white"
                        >
                            一覧を見る
                        </button>
                        <button
                            type="button"
                            wire:click="cancelDateAction"
                            class="rounded-lg bg-slate-500 px-4 py-2 text-sm font-semibold text-white"
                        >
                            キャンセル
                        </button>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-xl bg-white p-4 shadow-sm">
            <h2 class="mb-3 text-lg font-semibold text-slate-800">{{ \Carbon\CarbonImmutable::parse($selectedDate)->format('Y年n月j日') }} の出欠</h2>
            <div class="space-y-2">
                @forelse ($this->dailyMembers() as $member)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 p-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $member->name }}</p>
                            <p class="text-sm text-slate-500">{{ $member->company ?? '所属未設定' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                                {{ $member->daily_status }}
                            </span>
                            <button
                                type="button"
                                wire:click="markStatus({{ $member->id }}, '{{ \App\Models\Member::STATUS_PRESENT }}')"
                                class="rounded-lg bg-emerald-500 px-3 py-2 text-sm font-bold text-white"
                            >
                                出
                            </button>
                            <button
                                type="button"
                                wire:click="markStatus({{ $member->id }}, '{{ \App\Models\Member::STATUS_ABSENT }}')"
                                class="rounded-lg bg-rose-500 px-3 py-2 text-sm font-bold text-white"
                            >
                                欠
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">メンバーが登録されていません。</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
