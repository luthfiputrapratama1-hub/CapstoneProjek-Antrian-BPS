<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterController extends Controller
{
    public function index(): View
    {
        $counters = Counter::with('services')->orderBy('counter_number')->get();
        $services = Service::where('is_active', true)->orderBy('order_priority')->get();
        return view('admin.counters.index', compact('counters', 'services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'counter_number' => ['required', 'integer', 'min:1', 'unique:counters,counter_number'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
        ], [
            'name.required' => 'Nama loket wajib diisi.',
            'counter_number.required' => 'Nomor loket wajib diisi.',
            'counter_number.unique' => 'Nomor loket tersebut sudah terdaftar.',
        ]);

        $counter = Counter::create([
            'name' => $validated['name'],
            'counter_number' => $validated['counter_number'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (!empty($validated['service_ids'])) {
            $counter->services()->sync($validated['service_ids']);
        }

        return redirect()->route('admin.counters.index')->with('success', "Loket {$counter->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Counter $counter): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'counter_number' => ['required', 'integer', 'min:1', 'unique:counters,counter_number,' . $counter->id],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['exists:services,id'],
        ], [
            'name.required' => 'Nama loket wajib diisi.',
            'counter_number.required' => 'Nomor loket wajib diisi.',
            'counter_number.unique' => 'Nomor loket tersebut sudah terdaftar.',
        ]);

        $counter->update([
            'name' => $validated['name'],
            'counter_number' => $validated['counter_number'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $counter->services()->sync($validated['service_ids'] ?? []);

        return redirect()->route('admin.counters.index')->with('success', "Data {$counter->name} dan penugasan layanan berhasil diperbarui.");
    }

    public function toggleActive(Counter $counter): RedirectResponse
    {
        $counter->update([
            'is_active' => !$counter->is_active,
        ]);

        $statusText = $counter->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.counters.index')->with('success', "Status {$counter->name} berhasil {$statusText}.");
    }
}
