@extends('layouts.app')

@section('title', 'Generate Invoices')

@section('content')
    <section class="page-intro">
        <div>
            <p class="eyebrow">School finance</p>
            <h1>Generate invoices</h1>
            <p class="intro-copy">Create an individual billing statement for a student or batch generate invoices for an entire grade.</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="secondary-link">Cancel</a>
    </section>

    @if ($errors->any())
        <div class="alert alert-error" style="margin-bottom: 1.5rem;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
        <button type="button" id="tab-single" class="primary-button" onclick="switchMode('single')">Single student invoice</button>
        <button type="button" id="tab-batch" class="secondary-button" onclick="switchMode('batch')">Batch generate by grade</button>
    </div>

    <!-- SINGLE INVOICE FORM -->
    <div id="single-form-panel">
        <section class="content-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Individual billing</p>
                    <h2>New student invoice</h2>
                </div>
            </div>

            <form method="POST" action="{{ route('invoices.store') }}" class="form-grid">
                @csrf
                <input type="hidden" name="is_batch" value="0">

                <div class="full-width">
                    <label for="branch_id_single">Campus</label>
                    <select id="branch_id_single" name="branch_id" required onchange="window.location.href = '{{ route('invoices.create') }}?branch_id=' + this.value">
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="student_id">Student</label>
                    <select id="student_id" name="student_id" required>
                        <option value="">Choose student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected((string) old('student_id') === (string) $student->id)>
                                {{ $student->full_name }} ({{ $student->admission_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="academic_year_id_single">Academic year</label>
                    <select id="academic_year_id_single" name="academic_year_id" required>
                        <option value="">Choose academic year</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) old('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="term_id_single">Term / Semester (optional)</label>
                    <select id="term_id_single" name="term_id">
                        <option value="">Full Academic Year</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected((string) old('term_id') === (string) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="due_date_single">Payment due date</label>
                    <input id="due_date_single" type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required>
                </div>

                <!-- LINE ITEMS -->
                <div class="full-width" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <h3>Fee items</h3>
                    <p class="meta-line">Select the fee category and enter the billed amount.</p>

                    <div id="items-container">
                        <div class="form-grid" style="align-items: flex-end; margin-bottom: 0.75rem;">
                            <div>
                                <label>Category</label>
                                <select name="items[0][fee_category_id]" required>
                                    <option value="">Choose fee category</option>
                                    @foreach ($feeCategories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Description</label>
                                <input type="text" name="items[0][description]" placeholder="e.g. Term 1 Tuition" required>
                            </div>
                            <div>
                                <label>Amount ($)</label>
                                <input type="number" step="0.01" min="0.01" name="items[0][amount]" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="full-width">
                    <label for="notes">Notes / Instructions (optional)</label>
                    <textarea id="notes" name="notes" rows="2" placeholder="Bank account details, late fee policy, or payment instructions">{{ old('notes') }}</textarea>
                </div>

                <div class="full-width form-actions">
                    <button type="submit" class="primary-button">Generate invoice</button>
                </div>
            </form>
        </section>
    </div>

    <!-- BATCH INVOICE FORM -->
    <div id="batch-form-panel" style="display: none;">
        <section class="content-card">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Grade-level mass billing</p>
                    <h2>Batch invoice generation</h2>
                </div>
            </div>

            <p style="margin-bottom: 1.5rem; color: #4b5563;">
                This tool automatically creates an invoice for all active students enrolled in the selected grade and campus, populated with the active fee structures assigned to them. Students who already have an invoice for this term will be safely skipped.
            </p>

            <form method="POST" action="{{ route('invoices.store') }}" class="form-grid">
                @csrf
                <input type="hidden" name="is_batch" value="1">

                <div class="full-width">
                    <label for="branch_id_batch">Campus</label>
                    <select id="branch_id_batch" name="branch_id" required onchange="window.location.href = '{{ route('invoices.create') }}?branch_id=' + this.value">
                        <option value="">Choose campus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $selectedBranchId) === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="grade_id_batch">Grade / Class</label>
                    <select id="grade_id_batch" name="grade_id" required>
                        <option value="">Choose grade</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" @selected((string) old('grade_id') === (string) $grade->id)>{{ $grade->name }} (Level {{ $grade->level }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="academic_year_id_batch">Academic year</label>
                    <select id="academic_year_id_batch" name="academic_year_id" required>
                        <option value="">Choose academic year</option>
                        @foreach ($academicYears as $year)
                            <option value="{{ $year->id }}" @selected((string) old('academic_year_id') === (string) $year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="term_id_batch">Term / Semester (optional)</label>
                    <select id="term_id_batch" name="term_id">
                        <option value="">Full Academic Year</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}" @selected((string) old('term_id') === (string) $term->id)>{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="due_date_batch">Payment due date</label>
                    <input id="due_date_batch" type="date" name="due_date" value="{{ old('due_date', now()->addDays(30)->toDateString()) }}" required>
                </div>

                <div class="full-width form-actions">
                    <button type="submit" class="primary-button" onclick="return confirm('Are you sure you want to generate invoices for all students in this grade?');">
                        Run batch invoicing
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        function switchMode(mode) {
            const singlePanel = document.getElementById('single-form-panel');
            const batchPanel = document.getElementById('batch-form-panel');
            const tabSingle = document.getElementById('tab-single');
            const tabBatch = document.getElementById('tab-batch');

            if (mode === 'batch') {
                singlePanel.style.display = 'none';
                batchPanel.style.display = 'block';
                tabSingle.className = 'secondary-button';
                tabBatch.className = 'primary-button';
            } else {
                singlePanel.style.display = 'block';
                batchPanel.style.display = 'none';
                tabSingle.className = 'primary-button';
                tabBatch.className = 'secondary-button';
            }
        }
    </script>
@endsection
