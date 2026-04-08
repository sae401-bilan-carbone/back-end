<?php

namespace App\Service;

class CarbonCalculatorService
{
    private const RATIOS_FOOD = [
        'vegetarian' => 0.5,
        'vegan'      => 0.3,
        'red_meat'   => 3.5,
        'white_meat' => 1.1,
        'fish'       => 0.8,
    ];

    private const RATIOS_SHOPPING = [
        'daily'     => 0.05, 
        'furniture' => 0.4,  
        'tech'      => 1.2,  
        'fashion'   => 0.6,  
    ];

    private const RATIOS_VEHICLE = [
        'car'        => 0.15,
        'motorbike'  => 0.1,
        'bus'        => 0.03,
        'train'      => 0.01,
        'plane'      => 0.25,
    ];

    private const RATIOS_ENERGY = [
        'diesel'     => 0.05,
        'gasoline'   => 0.04,
        'electric'   => 0.01,
        'hydrogen'   => 0.005,
        'none'       => 0, 
    ];

    public function calculate(string $type, array $data): float
    {
        return match ($type) {
            'shopping' => $this->calculateShopping($data),
            'food'     => $this->calculateFood($data),
            'journey'  => $this->calculateJourney($data),
            default    => 0.0,
        };
    }

    private function calculateJourney(array $data): float
    {
        $vehicle = $data['vehicle'] ?? 'car';
        $energy  = $data['energy'] ?? 'none';
        $dist    = (float) ($data['distance'] ?? 0);

        $vCoeff = self::RATIOS_VEHICLE[$vehicle] ?? self::RATIOS_VEHICLE['car'];
        $eCoeff = self::RATIOS_ENERGY[$energy]   ?? 0;

        $total = ($vCoeff + $eCoeff) * $dist;

        return ($data['round_trip'] ?? false) ? $total * 2 : $total;
    }

    private function calculateFood(array $data): float
    {
        $total = 0.0;
        $meals = $data['meals'] ?? [];

        foreach ($meals as $meal) {
            $type  = $meal['type'] ?? 'vegetarian';
            $count = (int) ($meal['count'] ?? 1);
            
            $coeff = self::RATIOS_FOOD[$type] ?? self::RATIOS_FOOD['vegetarian'];
            $total += ($coeff * $count);
        }

        return $total;
    }

    private function calculateShopping(array $data): float
    {
        $total = 0.0;
        $items = $data['items'] ?? [];

        foreach ($items as $item) {
            $cat    = $item['category'] ?? 'daily';
            $amount = (float) ($item['amount'] ?? 0);

            $coeff = self::RATIOS_SHOPPING[$cat] ?? self::RATIOS_SHOPPING['daily'];
            $total += ($coeff * $amount);
        }

        return $total;
    }
}