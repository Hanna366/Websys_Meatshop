@extends('layouts.tenant')

@section('title', 'Suppliers - Meat Shop POS')
@section('page_title', 'Suppliers')
@section('page_subtitle', 'Manage vendor relationships, status, and incoming deliveries')

@section('header_actions')
    @if(session('permissions.data_export'))
        <button type="button" onclick="notify('Supplier export started.', 'success')" class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            <i data-lucide="file-down" class="h-4 w-4"></i>
            Export
        </button>
    @endif

    @if(session('permissions.supplier_management'))
        <button type="button" onclick="notify('New supplier form will open here.', 'info')" class="btn-primary-gradient inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Add Supplier
        </button>
    @endif
@endsection

@section('content')
<section class="space-y-6">
    @if(!session('permissions.supplier_management'))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Supplier management requires Standard plan or higher. <a href="/pricing" class="font-semibold underline">Upgrade now</a>.
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Suppliers</p><p class="mt-2 text-2xl font-bold text-slate-900">18</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Active</p><p class="mt-2 text-2xl font-bold text-emerald-600">15</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pending Orders</p><p class="mt-2 text-2xl font-bold text-indigo-600">7</p></article>
        <article class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card"><p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Deliveries Today</p><p class="mt-2 text-2xl font-bold text-amber-600">3</p></article>
    </div>

    <section class="rounded-3xl border border-white/70 bg-white/90 p-5 shadow-card sm:p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="heading-font text-lg font-semibold text-slate-900">Supplier List</h2>
            <input id="supplierSearch" type="text" placeholder="Search supplier..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 outline-none ring-0 transition focus:border-indigo-300 focus:shadow-sm sm:w-56">
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-3">ID</th>
                        <th class="px-3 py-3">Company</th>
                        <th class="px-3 py-3">Contact</th>
                        <th class="px-3 py-3">Phone</th>
                        <th class="px-3 py-3">Products</th>
                        <th class="px-3 py-3">Status</th>
                    </tr>
                </thead>
                <tbody id="suppliersTable" class="divide-y divide-slate-100 text-slate-700">
                    <tr><td class="px-3 py-3">#S001</td><td class="px-3 py-3 font-medium">Beef Masters Inc.</td><td class="px-3 py-3">Antonio Dela Cruz</td><td class="px-3 py-3">+63 912 3456</td><td class="px-3 py-3">Beef Products</td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span></td></tr>
                    <tr><td class="px-3 py-3">#S003</td><td class="px-3 py-3 font-medium">Fresh Farms Ltd.</td><td class="px-3 py-3">Roberto Santos</td><td class="px-3 py-3">+63 918 2345</td><td class="px-3 py-3">Beef</td><td class="px-3 py-3"><span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Pending</span></td></tr>
                    <tr><td class="px-3 py-3">#S005</td><td class="px-3 py-3 font-medium">Quality Meats Supply</td><td class="px-3 py-3">Carlos Mendez</td><td class="px-3 py-3">+63 916 8901</td><td class="px-3 py-3">Mixed Products</td><td class="px-3 py-3"><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span></td></tr>
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection

@push('scripts')
<script>
function notify(message, icon = 'success') {
    if (window.Swal) {
        Swal.fire({ toast: true, position: 'top-end', timer: 2300, showConfirmButton: false, icon, title: message });
        return;
    }
    alert(message);
}

document.getElementById('supplierSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#suppliersTable tr').forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
@endpush