<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\AddGoalFundsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GoalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $goals = $user->goals()
            ->orderBy('target_date')
            ->get()
            ->map(function ($goal) {
                $target = $goal->target_amount;
                $current = $goal->current_amount;
                $percentage = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
                
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $target, // in cents
                    'current_amount' => $current, // in cents
                    'target_date' => $goal->target_date ? $goal->target_date->format('Y-m-d') : null,
                    'display_date' => $goal->target_date ? $goal->target_date->format('M d, Y') : 'No Date',
                    'percentage' => $percentage,
                    'remaining' => max(0, $target - $current),
                ];
            });

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'currency' => $user->currency,
        ]);
    }

    public function store(StoreGoalRequest $request)
    {
        $validated = $request->validated();

        // Convert dollars to cents
        $validated['target_amount'] = (int) round($validated['target_amount'] * 100);
        $validated['current_amount'] = isset($validated['current_amount']) ? (int) round($validated['current_amount'] * 100) : 0;

        $request->user()->goals()->create($validated);

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    /**
     * Add funds to a goal.
     */
    public function addFunds(AddGoalFundsRequest $request, Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validated();
        $amountInCents = (int) round($validated['amount'] * 100);

        DB::transaction(function () use ($goal, $amountInCents, $validated) {
            $lockedGoal = Goal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            if ($validated['operation'] === 'add') {
                $lockedGoal->increment('current_amount', $amountInCents);
            } else {
                // Ensure we don't go below 0
                $newAmount = max(0, $lockedGoal->current_amount - $amountInCents);
                $lockedGoal->update(['current_amount' => $newAmount]);
            }
        });

        return redirect()->back()->with('success', 'Funds updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $goal->delete();

        return redirect()->back()->with('success', 'Savings goal removed successfully.');
    }
}
