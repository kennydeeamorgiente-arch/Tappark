<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class DeactivateInactiveUsers extends BaseCommand
{
    protected $group       = 'Users';
    protected $name        = 'users:deactivate-inactive';
    protected $description = 'Deactivate user accounts that have had no activity for 1 year.';

    public function run(array $params)
    {
        $db = Database::connect();

        $cutoff = date('Y-m-d H:i:s', strtotime('-1 year'));

        $builder = $db->table('users');

        $builder->set([
            'status' => 'inactive',
            'is_online' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $builder->whereNotIn('user_type_id', [2, 3]);
        $builder->groupStart()
            ->where('last_activity_at <', $cutoff)
            ->orGroupStart()
                ->where('last_activity_at', null)
                ->where('created_at <', $cutoff)
            ->groupEnd()
        ->groupEnd();

        $db->transStart();
        $builder->update();
        $affected = $db->affectedRows();
        $db->transComplete();

        if ($db->transStatus() === false) {
            CLI::error('Failed to deactivate inactive users.');
            return;
        }

        CLI::write('Deactivated inactive users: ' . (int)$affected);
    }
}
