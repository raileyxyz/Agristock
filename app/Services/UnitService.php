<?php

namespace App\Services;

use App\Models\Unit;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\Log;

class UnitService
{
    public function getUnits($search = null)
    {
        return Unit::query()
            ->withCount('products')
            ->when($search, function($query) use ($search){
                $query->where('name','like',"%{$search}%")
                    ->orWhere('abbreviation','like',"%{$search}%");
            })
            ->get();
    }

    public function create(array $data)
    {
        return Unit::create($data);
    }

    public function update(Unit $unit, array $data)
    {
        return $unit->update($data);
    }

    public function delete(Unit $unit): array
    {
        try {
            if ($unit->products()->exists()) {
                throw new BusinessRuleException("Cannot delete \"{$unit->name}\" — it is still used by one or more products.");
            }

            $unit->delete();

            return ['success' => true, 'message' => "{$unit->name} deleted successfully."];
        } catch (BusinessRuleException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        } catch (\Throwable $e) {
            Log::error('Failed to delete unit', ['unit_id' => $unit->id, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Something went wrong. Please try again.'];
        }
    }
}
