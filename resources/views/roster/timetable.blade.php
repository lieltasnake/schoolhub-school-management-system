@extends('layouts.app')

@section('title', 'Class Timetable Roster')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Roster</p>
            <h1>Class timetable</h1>
            <p class="intro-copy">Visual weekly timetable grid showing scheduled subjects, teachers, and classrooms.</p>
        </div>
    </section>

    <section class="content-card">
        <form method="GET" action="{{ route('roster.timetable') }}" class="filter-form">
            <select name="section_id" aria-label="Select class section" onchange="this.form.submit()">
                <option value="">Select section</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected((string) $selectedSectionId === (string) $section->id)>
                        {{ $section->grade->name }} &bull; {{ $section->name }}
                    </option>
                @endforeach
            </select>
            <noscript><button type="submit" class="secondary-button">View schedule</button></noscript>
        </form>

        @if ($selectedSectionId && $periods->isNotEmpty())
            <div class="table-wrap" style="margin-top:1.5rem; overflow-x:auto;">
                <table class="data-table" style="min-width:750px;">
                    <thead>
                        <tr>
                            <th style="width:120px;">Day</th>
                            @foreach ($periods as $period)
                                <th style="text-align:center;">
                                    <div>{{ $period->name }}</div>
                                    <small style="font-weight:normal; opacity:0.8;">{{ $period->start_time }} – {{ $period->end_time }}</small>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $day)
                            <tr>
                                <td><strong>{{ $day }}</strong></td>
                                @foreach ($periods as $period)
                                    @php
                                        $slot = $schedule[$day][$period->id] ?? null;
                                    @endphp
                                    <td style="text-align:center; vertical-align:middle; padding:0.6rem 0.5rem; background: {{ $period->is_break ? 'rgba(0,0,0,0.02)' : 'transparent' }};">
                                        @if ($period->is_break)
                                            <span style="font-size:0.8rem; color:var(--text-muted, #94a3b8); font-style:italic;">Break</span>
                                        @elseif ($slot)
                                            <div style="font-weight:600; color:var(--primary, #0f172a);">{{ $slot->subject->name }}</div>
                                            <div style="font-size:0.8rem; color:var(--text-muted, #64748b);">{{ $slot->teacher->user->name }}</div>
                                            @if ($slot->classroom)
                                                <small style="font-size:0.75rem; background:rgba(0,0,0,0.05); padding:1px 6px; border-radius:4px;">{{ $slot->classroom->room_number }}</small>
                                            @endif
                                        @else
                                            <span style="color:var(--text-muted, #cbd5e1);">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="text-align:center; padding:3rem 1rem; color:var(--text-muted, #64748b);">
                Please select a section above or ensure bell periods are configured to view the timetable grid.
            </p>
        @endif
    </section>
@endsection
