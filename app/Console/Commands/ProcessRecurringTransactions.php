<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring-transactions:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active recurring transactions that are due today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        
        $recurringTxs = RecurringTransaction::where('is_active', true)
            ->whereDate('next_run_date', '<=', $today->format('Y-m-d'))
            ->with(['user', 'category', 'card'])
            ->get();

        $this->info("Found " . $recurringTxs->count() . " active recurring transaction templates due for processing.");

        foreach ($recurringTxs as $recurringTx) {
            $user = $recurringTx->user;
            
            // Instantiate User-Scoped TransactionService manually (since no CLI user session exists)
            $transactionService = new TransactionService($user);
            
            // Create the real transaction
            $transactionService->storeTransaction([
                'title' => $recurringTx->title,
                'type' => $recurringTx->type,
                'amount' => $recurringTx->amount, // already in cents
                'category_id' => $recurringTx->category_id,
                'card_id' => $recurringTx->card_id,
                'recipient_name' => $recurringTx->recipient_name ?? ($recurringTx->type === 'expense' ? 'N/A' : $recurringTx->title),
                'notes' => $recurringTx->notes ? "Recurring: " . $recurringTx->notes : "Generated automatically from template: " . $recurringTx->title,
                'status' => 'completed',
                'transaction_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            // Catch up the next run date to the future (tomorrow or later)
            $nextRun = Carbon::parse($recurringTx->next_run_date);
            while ($nextRun->lte($today)) {
                switch ($recurringTx->frequency) {
                    case 'daily':
                        $nextRun->addDay();
                        break;
                    case 'weekly':
                        $nextRun->addWeek();
                        break;
                    case 'bi-weekly':
                        $nextRun->addWeeks(2);
                        break;
                    case 'monthly':
                        $nextRun->addMonth();
                        break;
                    case 'yearly':
                        $nextRun->addYear();
                        break;
                    default:
                        $nextRun->addMonth();
                        break 2; // Exit loop if unknown frequency
                }
            }

            // Update scheduling state on the template
            $recurringTx->update([
                'last_run_date' => $today->format('Y-m-d'),
                'next_run_date' => $nextRun->format('Y-m-d'),
            ]);

            $this->line("Successfully processed recurring transaction: '{$recurringTx->title}' for User: {$user->email}");
        }

        $this->info("Recurring transactions processing completed successfully.");
    }
}
