<?php

namespace App\Models;

use CodeIgniter\Model;

class ShoeSizeStockModel extends Model
{
    protected $table            = 'shoe_size_stock';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['shoe_id', 'size', 'quantity', 'created_at', 'updated_at'];
    protected $useTimestamps    = true;

    public function getAllQuantities(): array
    {
        $rows = $this->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['shoe_id']][$row['size']] = (int) $row['quantity'];
        }
        return $map;
    }

    public function getQuantitiesForShoe(int $shoeId): array
    {
        $rows = $this->where('shoe_id', $shoeId)->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['size']] = (int) $row['quantity'];
        }
        return $map;
    }

    public function addQuantities(int $shoeId, array $entries): void
    {
        foreach ($entries as $entry) {
            $existing = $this->where('shoe_id', $shoeId)
                             ->where('size', $entry['size'])
                             ->first();
            if ($existing) {
                $this->update($existing['id'], [
                    'quantity' => (int) $existing['quantity'] + (int) $entry['quantity']
                ]);
            } else {
                $this->insert([
                    'shoe_id'  => $shoeId,
                    'size'     => $entry['size'],
                    'quantity' => (int) $entry['quantity']
                ]);
            }
        }
    }

    public function reduceQuantities(int $shoeId, array $entries): void
    {
        foreach ($entries as $entry) {
            $existing = $this->where('shoe_id', $shoeId)
                             ->where('size', $entry['size'])
                             ->first();
            if ($existing) {
                $newQty = max(0, (int) $existing['quantity'] - (int) $entry['quantity']);
                $this->update($existing['id'], [
                    'quantity' => $newQty,
                ]);
            }
        }
    }

    public function setQuantities(int $shoeId, array $entries): void
    {
        foreach ($entries as $entry) {
            $existing = $this->where('shoe_id', $shoeId)
                             ->where('size', $entry['size'])
                             ->first();
            $qty = max(0, (int) $entry['quantity']);
            if ($existing) {
                $this->update($existing['id'], [
                    'quantity' => $qty,
                ]);
            } else {
                $this->insert([
                    'shoe_id'  => $shoeId,
                    'size'     => $entry['size'],
                    'quantity' => $qty,
                ]);
            }
        }
    }

    public function removeSizes(int $shoeId, array $removedSizes): void
    {
        if (!empty($removedSizes)) {
            $this->where('shoe_id', $shoeId)
                 ->whereIn('size', $removedSizes)
                 ->delete();
        }
    }
}
