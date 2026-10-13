@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">Curriculum</p>
            <h1>Subjects</h1>
            <p class="intro-copy">Manage school subjects, course codes, departments, and credit hours.</p>
        </div>
        <a href="{{ route('subjects.create') }}" class="primary-link">Add subject</a>
    </section>

    <section class="content-card">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('subjects.index') }}" class="filter-form">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or department" aria-label="Search subjects">
            @if ($branches->count() > 1)
                <select name="branch_id" aria-label="Filter by campus">
                    <option value="">All campuses</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach (['active', 'inactive'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="secondary-button">Apply filters</button>
        </form>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>Campus</th>
                        <th>Department</th>
                        <th>Credit hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjects as $subject)
                        <tr>
                            <td><strong>{{ $subject->name }}</strong></td>
                            <td><code>{{ $subject->code }}</code></td>
                            <td>{{ $subject->branch->name }}</td>
                            <td>{{ $subject->department ?? 'General' }}</td>
                            <td>{{ $subject->credit_hours ?? '—' }}</td>
                            <td><span class="status-pill {{ $subject->status }}">{{ ucfirst($subject->status) }}</span></td>
                            <td>
                                <a href="{{ route('subjects.edit', $subject) }}" class="table-link">Edit</a>
                                <form method="POST" action="{{ route('subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="table-link" style="background:none; border:none; cursor:pointer; color:var(--danger, #ef4444); padding:0;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No subjects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $subjects->links() }}
    </section>
@endsection
