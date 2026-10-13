@extends('layouts.app')

@section('title', $feeStructure ? 'Edit Fee Structure' : 'Create Fee Structure')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>{{ $feeStructure ? 'Edit fee structure' : 'Create fee structure' }}</h1>
            <p class="intro-copy">Set the price, academic year, and applicable grade level for this fee category.</p>
        </div>
        <a href="{{ $feeStructure ? route('fee-structures.show', $feeStructure) : route('fee-structures.index') }}" class="secondary-link">Cancel</a>
    </section>

    <section class="content-card">
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $feeStructure ? route('fee-structures.update', $feeStructure) : route('fee-structures.store') }}" class="form-grid">
            @csrf
            @if ($feeStructure)
                @method('PUT')
            @endif

            @unless ($feeStructure)
                <div class="full-width">
                    <label for="branch_id">Campus</label>
                    <select id="branch_id" name="branch_id" required onchange="window.location.href = '{{ route('fee-structures.create') }}?branch_id=' + this.value">
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label for="fee_category_id">Fee category</label>
                <select id="fee_category_id" name="fee_category_id" required>
                    <option value="">Choose category</option>
                    @foreach ($feeCategories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('fee_category_id', $feeStructure?->fee_category_id) === (string) $category->id)>
                            {{ $category->name }} ({{ $category->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="grade_id">Grade / Class</label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Choose grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected((string) old('grade_id', $feeStructure?->grade_id) === (string) $grade->id)>
                            {{ $grade->name }} (Level {{ $grade->level }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="academic_year_id">Academic year</label>
                <select id="academic_year_id" name="academic_year_id" required>
                    <option value="">Choose academic year</option>
                    @foreach ($academicYears as $year)
                        <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $feeStructure?->academic_year_id) === (string) $year->id)>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="term_id">Term / Semester (optional)</label>
                <select id="term_id" name="term_id">
                    <option value="">Full Academic Year (No term)</option>
                    @foreach ($terms as $term)
                        <option value="{{ $term->id }}" @selected((string) old('term_id', $feeStructure?->term_id) === (string) $term->id)>
                            {{ $term->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="amount">Fee amount ($)</label>
                <input id="amount" type="number" step="0.01" min="0" max="999999999999.99" name="amount" value="{{ old('amount', $feeStructure?->amount) }}" placeholder="0.00" required>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" @selected(old('status', $feeStructure?->status ?? 'active') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $feeStructure?->status) === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="full-width">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" rows="3" placeholder="Specific notes about this fee assignment">{{ old('description', $feeStructure?->description) }}</textarea>
            </div>

            <div class="full-width form-actions">
                <button type="submit" class="primary-button">{{ $feeStructure ? 'Save changes' : 'Create fee structure' }}</button>
            </div>
        </form>
    </section>
@endsection
