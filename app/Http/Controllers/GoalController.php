<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use Illuminate\Http\Request;
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0.01',
            'current_amount' => 'nullable|numeric|min:0',
            'target_date' => 'required|date|after_or_equal:today',
        ]);

        // Convert dollars to cents
        $validated['target_amount'] = (int) round($validated['target_amount'] * 100);
        $validated['current_amount'] = isset($validated['current_amount']) ? (int) round($validated['current_amount'] * 100) : 0;

        $request->user()->goals()->create($validated);

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    /**
     * Add funds to a goal.
     */
    public function addFunds(Request $request, Goal $goal)
    {
        if ($goal->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'operation' => 'required|in:add,withdraw',
        ]);

        $amountInCents = (int) round($validated['amount'] * 100);

        if ($validated['operation'] === 'add') {
            $goal->increment('current_amount', $amountInCents);
        } else {
            // Ensure we don't go below 0
            $newAmount = max(0, $goal->current_amount - $amountInCents);
            $goal->update(['current_amount' => $newAmount]);
        }

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
