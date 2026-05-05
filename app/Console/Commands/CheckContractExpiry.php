<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use App\Models\User;
use App\Mail\ContractExpiryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CheckContractExpiry extends Command
{
    protected $signature = 'hr:check-contracts';
    protected $description = 'Check for expiring contracts and notify Admins/HR.';

    public function handle()
    {
        $this->info('Checking for expiring contracts...');

        // Contracts expiring in exactly 30 days
        $targetDate = Carbon::today()->addDays(30)->toDateString();

        $expiringContracts = Contract::with('employee.user')
            ->where('status', 'active')
            ->where('end_date', $targetDate)
            ->get();

        if ($expiringContracts->isEmpty()) {
            $this->info('No contracts expiring in 30 days.');
            return self::SUCCESS;
        }

        // Get HR & Admin emails
        $notifiableEmails = User::whereIn('role', ['admin', 'hr'])
            ->where('status', 'approved')
            ->pluck('email')
            ->toArray();

        if (empty($notifiableEmails)) {
            $this->warn('No admin/hr emails found to notify.');
            return self::FAILURE;
        }

        foreach ($expiringContracts as $contract) {
            try {
                Mail::to($notifiableEmails)->send(new ContractExpiryMail($contract));
                $this->info("Notified admins about contract expiry for: {$contract->employee->employee_code}");
            } catch (\Exception $e) {
                $this->error("Failed to send email for {$contract->id}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
