<?php

namespace App\Services;

use App\Models\Unit;

class UnitService
{
    public function getUnits($search = null)
    {
        return Unit::query()

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



    public function delete(Unit $unit)
    {
        return $unit->delete();
    }
}
