<?php

namespace RMS\Core\Examples\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RMS\Core\Data\Database;
use RMS\Core\Data\Field;
use RMS\Core\Form\FormGenerator;
use RMS\Core\List\ListGenerator;

/**
 * Minimal Example Controller
 * 
 * This demonstrates the absolute minimum code needed to create
 * a working CRUD interface with RMS Core.
 */
class MinimalExampleController
{
    /**
     * Complete users list in just a few lines
     */
    public function usersList(Request $request): JsonResponse
    {
        // Create database query with fields
        $database = Database::fromTable('users', ['id', 'name', 'email', 'created_at']);
        
        // Apply search if provided
        if ($request->has('search')) {
            $database->search($request->get('search'), ['name', 'email']);
        }
        
        // Get paginated results
        $results = $database->sort('created_at', 'DESC')->get(15);
        
        return response()->json([
            'data' => $results->items(),
            'total' => $results->total()
        ]);
    }

    /**
     * Complete form generation in minimal code
     */
    public function usersForm(int $id = null): JsonResponse
    {
        $fields = [
            Field::make('name')->setRequired(true)->setTitle('Name'),
            Field::make('email')->setRequired(true)->setTitle('Email'),
            Field::make('active')->setType(Field::BOOL)->setTitle('Active'),
        ];

        $formGenerator = new FormGenerator($fields);
        
        // Load existing data if editing
        if ($id) {
            $data = Database::fromTable('users', ['name', 'email', 'active'])
                ->where('id', '=', $id)
                ->get(1)
                ->first();
            $formGenerator->setExistingData($data?->toArray() ?? []);
        }
        
        return response()->json(['form' => $formGenerator->render()]);
    }

    /**
     * Advanced filtering in just a few lines
     */
    public function filteredList(Request $request): JsonResponse
    {
        $database = Database::fromTable('posts', ['id', 'title', 'content', 'user_id', 'created_at']);
        
        // Join with users table
        $database->leftJoin('users', 'posts.user_id', '=', 'users.id');
        
        // Apply filters from request
        if ($request->has('user_id')) {
            $database->where('posts.user_id', '=', $request->get('user_id'));
        }
        
        if ($request->has('published')) {
            $database->where('posts.published', '=', $request->get('published'));
        }
        
        // Date range filter
        if ($request->has('date_from')) {
            $database->where('posts.created_at', '>=', $request->get('date_from'));
        }
        
        $results = $database->sort('posts.created_at', 'DESC')->get(20);
        
        return response()->json(['data' => $results->items()]);
    }

    /**
     * Statistics example with aggregation
     */
    public function userStats(): JsonResponse
    {
        // Count active users
        $activeUsers = Database::fromTable('users', ['COUNT(*) as count'])
            ->where('active', '=', 1)
            ->get(1)
            ->first()
            ->count;

        // Users by role
        $usersByRole = Database::fromTable('users', ['role', 'COUNT(*) as count'])
            ->where('active', '=', 1)
            ->groupBy('role')
            ->sort('count', 'DESC')
            ->get(10);

        return response()->json([
            'active_users' => $activeUsers,
            'users_by_role' => $usersByRole->items()
        ]);
    }

    /**
     * Export example
     */
    public function exportUsers(Request $request): JsonResponse
    {
        $database = Database::fromTable('users', [
            'id', 'name', 'email', 'active', 
            'DATE(created_at) as created_date'
        ]);
        
        // Apply same filters as list
        if ($request->has('active')) {
            $database->where('active', '=', $request->get('active'));
        }
        
        $results = $database->sort('created_at', 'DESC')->get(1000);
        
        return response()->json([
            'export_data' => $results->items(),
            'total_exported' => $results->count()
        ]);
    }
}

/**
 * Even simpler - One-liner examples:
 */
class OneLineExamples
{
    public function allUsers(): array
    {
        return Database::fromTable('users')->get()->items();
    }
    
    public function activeUsers(): array
    {
        return Database::fromTable('users')->where('active', '=', 1)->get()->items();
    }
    
    public function searchUsers(string $term): array
    {
        return Database::fromTable('users')->search($term, ['name', 'email'])->get()->items();
    }
    
    public function usersByRole(string $role): array
    {
        return Database::fromTable('users')->where('role', '=', $role)->sort('name')->get()->items();
    }
    
    public function recentUsers(int $days = 30): array
    {
        return Database::fromTable('users')
            ->where('created_at', '>=', now()->subDays($days))
            ->sort('created_at', 'DESC')
            ->get()
            ->items();
    }
}
