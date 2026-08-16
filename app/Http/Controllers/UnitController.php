<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\UnitService;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;


class UnitController extends Controller
{
    public function __construct(
        protected UnitService $unitService
    ) {}

    public function index(Request $request)
    {
        $units = $this->unitService->getUnits($request->search);

        return view('units.index', compact('units'));
    }

    public function store(StoreUnitRequest $request)
    {
        $this->unitService->create($request->validated());


        return redirect()->route('units.index')->with('success','Unit created successfully.');

    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $this->unitService->update($unit, $request->validated());


        return redirect()->route('units.index')->with('success',"{$unit->name} updated successfully.");
    }

    public function destroy(Unit $unit)
    {
        try {
            $this->unitService->delete($unit);
        } catch (\Exception $e) {
            return redirect()->route('units.index')->with('error', $e->getMessage());
        }

        return redirect()->route('units.index')->with('success', "{$unit->name} deleted successfully.");
    }

}
