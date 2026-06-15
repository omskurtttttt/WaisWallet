<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Http\Requests\AddGoalFundsRequest;
use App\Services\GoalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class GoalController extends Controller
{
    protected GoalService $service;

    public function __construct(GoalService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $goals = $this->service->getGoals();

        return Inertia::render('Goals/Index', [
            'goals' => $goals,
            'currency' => $user->currency,
        ]);
    }

    public function store(StoreGoalRequest $request)
    {
        $this->service->storeGoal($request->validated());

        return redirect()->back()->with('success', 'Savings goal created successfully.');
    }

    /**
     * Update the specified goal.
     */
    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $this->service->updateGoal($goal, $request->validated());

        return redirect()->back()->with('success', 'Savings goal updated successfully.');
    }

    /**
     * Add funds to a goal.
     */
    public function addFunds(AddGoalFundsRequest $request, Goal $goal)
    {
        Gate::authorize('update', $goal);

        $this->service->addFunds($goal, $request->validated());

        return redirect()->back()->with('success', 'Funds updated successfully.');
    }

    /**
     * Mark the specified goal as complete.
     */
    public function markAsComplete(Goal $goal)
    {
        Gate::authorize('update', $goal);

        $this->service->markAsComplete($goal);

        return redirect()->back()->with('success', 'Savings goal completed successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        Gate::authorize('delete', $goal);

        $this->service->deleteGoal($goal);

        return redirect()->back()->with('success', 'Savings goal removed successfully.');
    }
}
