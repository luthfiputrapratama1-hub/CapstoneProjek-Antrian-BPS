<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::withCount('queues')->orderBy('order_priority')->get();
        return view('admin.services.index', compact('services'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code_prefix' => ['required', 'string', 'max:4', 'uppercase', 'unique:services,code_prefix'],
            'description' => ['nullable', 'string'],
            'order_priority' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama layanan wajib diisi.',
            'code_prefix.required' => 'Kode prefix layanan (misal: A, B) wajib diisi.',
            'code_prefix.unique' => 'Kode prefix tersebut sudah digunakan oleh layanan lain.',
            'order_priority.required' => 'Urutan prioritas layanan wajib diisi.',
        ]);

        Service::create([
            'name' => $validated['name'],
            'code_prefix' => strtoupper($validated['code_prefix']),
            'description' => $validated['description'] ?? null,
            'order_priority' => $validated['order_priority'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Layanan PST baru berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code_prefix' => ['required', 'string', 'max:4', 'uppercase', 'unique:services,code_prefix,' . $service->id],
            'description' => ['nullable', 'string'],
            'order_priority' => ['required', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama layanan wajib diisi.',
            'code_prefix.required' => 'Kode prefix layanan wajib diisi.',
            'code_prefix.unique' => 'Kode prefix tersebut sudah digunakan oleh layanan lain.',
        ]);

        $service->update([
            'name' => $validated['name'],
            'code_prefix' => strtoupper($validated['code_prefix']),
            'description' => $validated['description'] ?? null,
            'order_priority' => $validated['order_priority'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.services.index')->with('success', "Layanan {$service->code_prefix} berhasil diperbarui.");
    }

    public function toggleActive(Service $service): RedirectResponse
    {
        $service->update([
            'is_active' => !$service->is_active,
        ]);

        $statusText = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('admin.services.index')->with('success', "Status layanan {$service->code_prefix} berhasil {$statusText}.");
    }
}
