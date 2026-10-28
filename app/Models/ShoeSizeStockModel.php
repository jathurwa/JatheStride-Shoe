<?php

namespace App\Models;

use CodeIgniter\Model;

class ShoeSizeStockModel extends Model
{
    protected $table            = 'shoe_size_stock';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['shoe_id', 'size', 'quantity'];
    protected $useTimestamps   = false;

    public function getQuantitiesForShoe(int $shoeId): array
    {
        $rows = $this->select('size, quantity')->where('shoe_id', $shoeId)->findAll();
        $quantities = [];

        foreach ($rows as $row) {
            $quantities[$row['size']] = (int) $row['quantity'];
        }

        return $quantities;
    }

    public function getAllQuantities(): array
    {
        $rows = $this->select('shoe_id, size, quantity')->findAll();
        $quantities = [];

        foreach ($rows as $row) {
            $quantities[$row['shoe_id']][$row['size']] = (int) $row['quantity'];
        }

        return $quantities;
    }

    public function addQuantities(int $shoeId, array $entries): void
    {
        foreach ($entries as $entry) {
            $existing = $this->where('shoe_id', $shoeId)
                ->where('size', $entry['size'])
                ->first();

            if ($existing) {
                $this->update($existing['id'], [
                    'quantity' => (int) $existing['quantity'] + (int) $entry['quantity'],
                ]);
                continue;
            }

            $this->insert([
                'shoe_id'  => $shoeId,
                'size'     => $entry['size'],
                'quantity' => (int) $entry['quantity'],
            ]);
        }
    }

    public function removeSizes(int $shoeId, array $sizes): void
    {
        if ($sizes === []) {
            return;
        }

        $this->where('shoe_id', $shoeId)
            ->whereNotIn('size', $sizes)
            ->delete();
    }
}