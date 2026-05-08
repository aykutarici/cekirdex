<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TableController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index()
    {
        $tables = CekirdexTable::where('cekirdex_restaurant_id', $this->rid())
            ->orderBy('id')->get();
        return view('cekirdex.panel.tables.index', compact('tables'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:64',
            'code'                 => 'nullable|string|max:32',
            'capacity'             => 'nullable|integer|min:1|max:60',
            'internal_note'        => 'nullable|string|max:2000',
            'accepts_reservations' => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'photo'                => 'nullable|image|max:3072',
        ]);

        $photo = $request->hasFile('photo')
            ? $request->file('photo')->store('cekirdex/tables', 'public')
            : null;

        CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->rid(),
            'name'                 => $data['name'],
            'code'                 => $data['code'] ?? null,
            'qr_token'             => CekirdexTable::newQrToken(),
            'capacity'             => $data['capacity'] ?? 2,
            'internal_note'        => $data['internal_note'] ?? null,
            'accepts_reservations' => $request->boolean('accepts_reservations'),
            'photo'                => $photo,
            'is_active'            => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Masa eklendi.');
    }

    public function update(Request $request, int $id)
    {
        $table = CekirdexTable::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $data = $request->validate([
            'name'                 => 'required|string|max:64',
            'code'                 => 'nullable|string|max:32',
            'capacity'             => 'nullable|integer|min:1|max:60',
            'internal_note'        => 'nullable|string|max:2000',
            'accepts_reservations' => 'nullable|boolean',
            'is_active'            => 'nullable|boolean',
            'photo'                => 'nullable|image|max:3072',
            'remove_photo'         => 'nullable|boolean',
        ]);

        if (!empty($data['remove_photo']) && $table->photo) {
            Storage::disk('public')->delete($table->photo);
            $table->photo = null;
        }
        if ($request->hasFile('photo')) {
            if ($table->photo) Storage::disk('public')->delete($table->photo);
            $table->photo = $request->file('photo')->store('cekirdex/tables', 'public');
        }

        $table->update([
            'name'                 => $data['name'],
            'code'                 => $data['code'] ?? null,
            'capacity'             => $data['capacity'] ?? $table->capacity,
            'internal_note'        => $data['internal_note'] ?? null,
            'accepts_reservations' => $request->has('accepts_reservations') ? $request->boolean('accepts_reservations') : $table->accepts_reservations,
            'is_active'            => $request->has('is_active') ? $request->boolean('is_active') : $table->is_active,
        ]);
        return back()->with('success', 'Masa güncellendi.');
    }

    public function destroy(int $id)
    {
        $table = CekirdexTable::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        if ($table->photo) Storage::disk('public')->delete($table->photo);
        $table->delete();
        return back()->with('success', 'Masa silindi.');
    }

    public function regenerateQr(int $id)
    {
        $table = CekirdexTable::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $table->update(['qr_token' => CekirdexTable::newQrToken()]);
        return back()->with('success', 'QR yenilendi.');
    }
}
