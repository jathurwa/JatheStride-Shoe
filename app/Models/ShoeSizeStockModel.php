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
    protected $useTimestamps    = false;

    public function ensureTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $db = $this->db ?? \Config\Database::connect();
            if (!$db->tableExists($this->table)) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id' => [
                        'type'           => 'INT',
                        'unsigned'       => true,
                        'auto_increment' => true,
                    ],
                    'shoe_id' => [
                        'type'     => 'INT',
                        'unsigned' => true,
                    ],
                    'size' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 20,
                    ],
                    'quantity' => [
                        'type'       => 'INT',
                        'unsigned'   => true,
                        'default'    => 0,
                    ],
                ]);
                $forge->addKey('id', true);
                $forge->addUniqueKey(['shoe_id', 'size'], 'uq_shoe_size_stock');
                $forge->createTable($this->table, true);
            }
            $checked = true;
        } catch (\Throwable $e) {
            log_message('error', 'ShoeSizeStockModel::ensureTable exception: ' . $e->getMessage());
        }
    }

    public function getAllQuantities(): array
    {
        $this->ensureTable();
        try {
            $rows = $this->findAll();
            $map = [];
            foreach ($rows as $row) {
                $map[$row['shoe_id']][$row['size']] = (int) $row['quantity'];
            }
            return $map;
        } catch (\Throwable $e) {
            log_message('error', 'ShoeSizeStockModel::getAllQuantities exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getQuantitiesForShoe(int $shoeId): array
    {
        $this->ensureTable();
        try {
            $rows = $this->where('shoe_id', $shoeId)->findAll();
            $map = [];
            foreach ($rows as $row) {
                $map[$row['size']] = (int) $row['quantity'];
            }
            return $map;
        } catch (\Throwable $e) {
            log_message('error', 'ShoeSizeStockModel::getQuantitiesForShoe exception: ' . $e->getMessage());
            return [];
        }
    }

    public function addQuantities(int $shoeId, array $entries): void
    {
        $this->ensureTable();
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
        $this->ensureTable();
        foreach ($entries as $entry) {
            $existing = $this->where('shoe_id', $shoeId)
                             ->where('size', $entry['size'])
                             ->first();
            if ($existing) {
                $newQty = max(0, (int) $existing['quantity'] - (int) $entry['quantity']);
                $this->update($existing['id'], [
                    'quantity' => $newQty,
                ]);
            } else {
                $this->insert([
                    'shoe_id'  => $shoeId,
                    'size'     => $entry['size'],
                    'quantity' => 0,
                ]);
            }
        }
    }

    public function setQuantities(int $shoeId, array $entries): void
    {
        $this->ensureTable();
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
        $this->ensureTable();
        if (!empty($removedSizes)) {
            $this->where('shoe_id', $shoeId)
                 ->whereIn('size', $removedSizes)
                 ->delete();
        }
    }
}
