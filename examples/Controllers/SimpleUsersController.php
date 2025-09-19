<?php

namespace RMS\Core\Examples\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use RMS\Core\Data\Database;
use RMS\Core\Data\Field;
use RMS\Core\Data\Column;
use RMS\Core\Form\FormGenerator;
use RMS\Core\List\ListGenerator;

/**
 * Simple Users Controller demonstrating basic RMS Core usage
 * 
 * This shows the minimal code needed to create a working list and form
 * using the new RMS architecture.
 */
class SimpleUsersController
{
    /**
     * Get users list with basic filtering and pagination
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Define fields for list display
        $fields = [
            Field::make('id')->setDatabaseKey('id')->setTitle('ID'),
            Field::make('name')->setDatabaseKey('name')->setTitle('Name'),
            Field::make('email')->setDatabaseKey('email')->setTitle('Email'),
            Field::make('active')->setDatabaseKey('active')->setTitle('Status'),
            Field::make('created_at')->setDatabaseKey('created_at')->setTitle('Created'),
        ];

        // Create database query
        $database = new Database($fields, 'users');
        
        // Apply basic filters
        if ($request->has('active')) {
            $database->where('active', '=', $request->get('active'));
        }
        
        // Apply search
        if ($request->has('search')) {
            $database->search($request->get('search'), ['name', 'email']);
        }
        
        // Apply sorting
        $database->sort($request->get('sort', 'created_at'), $request->get('direction', 'DESC'));
        
        // Get results
        $results = $database->get($request->get('per_page', 15));
        
        // Generate list
        $listGenerator = new ListGenerator($fields, $results);
        
        return response()->json([
            'success' => true,
            'data' => $listGenerator->render(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'total' => $results->total(),
                'per_page' => $results->perPage(),
            ]
        ]);
    }

    /**
     * Get form for creating/editing users
     * 
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function form(Request $request, int $id = null): JsonResponse
    {
        // Define form fields
        $fields = [
            Field::make('name')
                ->setTitle('Full Name')
                ->setType(Field::STRING)
                ->setRequired(true)
                ->setDatabaseKey('name'),
                
            Field::make('email')
                ->setTitle('Email Address')
                ->setType(Field::STRING)
                ->setRequired(true)
                ->setDatabaseKey('email'),
                
            Field::make('password')
                ->setTitle('Password')
                ->setType(Field::PASSWORD)
                ->setRequired($id === null) // Required only for new users
                ->setDatabaseKey('password'),
                
            Field::make('active')
                ->setTitle('Active Status')
                ->setType(Field::BOOL)
                ->setDatabaseKey('active')
                ->setDefaultValue(true),
        ];

        // Load existing data if editing
        $existingData = [];
        if ($id) {
            $database = new Database($fields, 'users');
            $database->where('id', '=', $id);
            $result = $database->get(1);
            
            if ($result->count() > 0) {
                $existingData = $result->first()->toArray();
                unset($existingData['password']); // Don't send password to frontend
            }
        }

        // Generate form
        $formGenerator = new FormGenerator($fields);
        $formGenerator->setExistingData($existingData);
        
        return response()->json([
            'success' => true,
            'form' => $formGenerator->render(),
            'data' => $existingData,
            'is_edit' => !empty($id)
        ]);
    }

    /**
     * Advanced list with multiple filters example
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function advancedList(Request $request): JsonResponse
    {
        $fields = [
            Field::make('id')->setDatabaseKey('users.id')->setTitle('ID'),
            Field::make('name')->setDatabaseKey('users.name')->setTitle('Name'),
            Field::make('email')->setDatabaseKey('users.email')->setTitle('Email'),
            Field::make('role_name')->setDatabaseKey('roles.name')->setTitle('Role'),
            Field::make('department_name')->setDatabaseKey('departments.name')->setTitle('Department'),
            Field::make('last_login')->setDatabaseKey('users.last_login_at')->setTitle('Last Login'),
            Field::make('posts_count')->setDatabaseKey('COUNT(posts.id)')->setMethodSql(true)->setTitle('Posts'),
        ];

        $database = new Database($fields, 'users');
        
        // Complex joins
        $database
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id');

        // Advanced filters using Column objects
        $filters = [];
        
        // Multiple role selection
        if ($request->has('roles') && is_array($request->get('roles'))) {
            $filters[] = new Column('users.role_id', 'IN', $request->get('roles'), Field::INTEGER);
        }
        
        // Date range for last login
        if ($request->has('login_from') && $request->has('login_to')) {
            $database->whereDateBetween('users.last_login_at', 
                $request->get('login_from'), 
                $request->get('login_to')
            );
        }
        
        // Users with no posts
        if ($request->get('has_no_posts')) {
            $filters[] = new Column('posts.id', 'IS NULL', null, Field::INTEGER);
        }
        
        // Active users only
        $filters[] = new Column('users.active', '=', 1, Field::BOOL);
        
        // Apply filters
        $database->withFilters($filters);
        
        // Group by user to handle the posts join
        $database->groupBy(['users.id', 'users.name', 'users.email', 'roles.name', 'departments.name', 'users.last_login_at']);
        
        // Sort by posts count
        $database->sort('posts_count', 'DESC');
        
        $results = $database->get($request->get('per_page', 20));
        
        return response()->json([
            'success' => true,
            'data' => $results->items(),
            'filters_applied' => $database->getAppliedFilters(),
            'sql_debug' => $database->toSql()
        ]);
    }

    /**
     * Demonstrate security features
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function secureList(Request $request): JsonResponse
    {
        $fields = [
            Field::make('id')->setDatabaseKey('id'),
            Field::make('name')->setDatabaseKey('name'),
            Field::make('email')->setDatabaseKey('email'),
        ];

        $database = new Database($fields, 'users');
        
        // Add security constraints (these are always applied)
        $database
            ->addSecurityConstraint('deleted_at', 'IS NULL', null)
            ->addSecurityConstraint('active', '=', 1)
            ->addSecurityConstraint('email_verified_at', 'IS NOT NULL', null);
        
        // User can only see their own data or their department's data
        if (!$request->user()->isAdmin()) {
            $database->addSecurityConstraint('department_id', '=', $request->user()->department_id);
        }
        
        $results = $database->get(15);
        
        return response()->json([
            'success' => true,
            'data' => $results->items(),
            'security_applied' => true
        ]);
    }
}
