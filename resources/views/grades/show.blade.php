@extends('layouts.app')

@section('title', $grade->name)

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">{{ $grade->branch->name }} / Grade</p>
            <h1>{{ $grade->name }}</h1>
            <p class="intro-copy">Code: {{ $grade->code }} &bull; Level: {{ $grade->level }}</p>
        </div>
        <div class="intro-actions">
            <a href="{{ route('grades.edit', $grade) }}" class="secondary-link">Edit</a>
            <a href="{{ route('sections.create', ['grade_id' => $grade->id]) }}" class="primary-link">Add section</a>
            <form method="POST" action="{{ route('grades.destroy', $grade) }}" class="inline-action" onsubmit="return confirm('Are you sure you want to delete this grade?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="table-link danger-link">Delete</button>
            </form>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Grade overview</p>
                <h2>Details</h2>
            </div>
            <span class="status-pill {{ $grade->status }}">{{ ucfirst($grade->status) }}</span>
        </div>
        <dl class="detail-list">
            <div><dt>Campus</dt><dd>{{ $grade->branch->name }}</dd></div>
            <div><dt>Grade code</dt><dd>{{ $grade->code }}</dd></div>
            <div><dt>Academic level</dt><dd>{{ $grade->level }}</dd></div>
            <div><dt>Total sections</dt><dd>{{ $grade->sections->count() }}</dd></div>
            @if ($grade->description)
                <div class="full-width"><dt>Description</dt><dd>{{ $grade->description }}</dd></div>
            @endif
        </dl>
    </section>

    <section class="content-card">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Class sections</p>
                <h2>Sections in {{ $grade->name }}</h2>
            </div>
            <a href="{{ route('sections.create', ['grade_id' => $grade->id]) }}" class="text-link">Add section</a>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Code</th>
                        <th>Academic year</th>
                        <th>Room</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grade->sections as $section)
                        <tr>
                            <td><strong>{{ $section->name }}</strong></td>
                            <td>{{ $section->code ?? '—' }}</td>
                            <td>{{ $section->academicYear?->name ?? 'All years' }}</td>
                            <td>{{ $section->room_number ?? '—' }}</td>
                            <td>{{ $section->capacity }}</td>
                            <td><span class="status-pill {{ $section->status }}">{{ ucfirst($section->status) }}</span></td>
                            <td>
                                <a href="{{ route('sections.show', $section) }}" class="table-link">View</a>
                                <a href="{{ route('sections.edit', $section) }}" class="table-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No sections configured for this grade yet. Click "Add section" to create one.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
