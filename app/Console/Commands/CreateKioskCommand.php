<?php

namespace App\Console\Commands;

use App\Services\KioskEnrollmentService;
use Illuminate\Console\Command;

class CreateKioskCommand extends Command
{
    protected $signature = 'kiosk:create
                            {name : Display name for the kiosk}
                            {--school= : School name}
                            {--location= : Physical location}
                            {--ip= : Allowed single IP address}
                            {--subnet= : Allowed CIDR subnet}';

    protected $description = 'Create a new kiosk record (admin setup until dashboard is available)';

    public function handle(KioskEnrollmentService $enrollment): int
    {
        $kiosk = $enrollment->createKiosk([
            'name' => $this->argument('name'),
            'school' => $this->option('school'),
            'location' => $this->option('location'),
            'allowed_ip' => $this->option('ip'),
            'allowed_subnet' => $this->option('subnet'),
        ]);

        $this->info('Kiosk created.');
        $this->line('ID: '.$kiosk->id);
        $this->line('UUID: '.$kiosk->kiosk_uuid);

        return self::SUCCESS;
    }
}
