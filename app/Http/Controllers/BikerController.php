<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\Way;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BikerController extends Controller
{
    public function index(): View
    {
        return view('admin.bikers', [
            'bikers' => Biker::query()->orderBy('name')->get(),
            'unassignedWays' => Way::query()
                ->whereNull('biker_id')
                ->whereDate('date', today())
                ->orderBy('id')
                ->get(),
            'assignedWays' => Way::query()
                ->whereNotNull('biker_id')
                ->whereDate('date', today())
                ->get()
                ->groupBy('biker_id'),
        ]);
    }

    public function assign(Request $request, Biker $biker): RedirectResponse
    {
        $data = $request->validate([
            'way_ids' => ['required', 'array', 'min:1'],
            'way_ids.*' => ['integer', 'exists:ways,id'],
        ]);

        Way::query()
            ->whereIn('id', $data['way_ids'])
            ->whereNull('biker_id')
            ->whereDate('date', today())
            ->update(['biker_id' => $biker->id]);

        return redirect()->route('admin.bikers')->with('biker_status', 'Ways assigned successfully.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('biker', [
            'name' => ['required', 'string', 'max:255'],
        ]);

        Biker::create($data);

        return redirect()->route('admin.bikers')->with('biker_status', 'Biker created successfully.');
    }

    public function update(Request $request, Biker $biker): RedirectResponse
    {
        $data = $request->validateWithBag('biker', [
            'name' => ['required', 'string', 'max:255'],
        ]);

        $biker->update($data);

        return redirect()->route('admin.bikers')->with('biker_status', 'Biker updated successfully.');
    }
}