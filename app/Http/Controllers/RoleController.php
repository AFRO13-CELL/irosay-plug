<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    /**
     * Read-only by design: the three roles and their permission sets are
     * seeded (RolePermissionSeeder, Phase 2) and enforced on every route via
     * CheckPermission middleware. This page exists so an Admin can SEE
     * exactly what each role can do — editing which permissions attach to
     * which role is a reasonable Phase 11 addition if ever needed, but the
     * spec's core requirement (backend enforcement) doesn't depend on a UI
     * editor existing.
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }
}
