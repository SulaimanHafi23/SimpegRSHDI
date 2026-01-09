#!/usr/bin/env php
<?php

/**
 * Script untuk check permission coverage di routes
 */

echo "🔍 CHECKING PERMISSION COVERAGE\n";
echo str_repeat("=", 60) . "\n\n";

// Get all admin routes
echo "📋 Getting all admin routes...\n";
$routesOutput = shell_exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan route:list 2>/dev/null');

// Extract admin routes
$routes = [];
$lines = explode("\n", $routesOutput);
foreach ($lines as $line) {
    if (preg_match('/admin\.(\w+)\.(\w+)/', $line, $matches)) {
        $module = $matches[1];
        $action = $matches[2];
        $routes[] = [
            'module' => $module,
            'action' => $action,
            'name' => "admin.$module.$action"
        ];
    }
}

$totalRoutes = count($routes);
echo "✅ Found $totalRoutes admin routes\n\n";

// Get all permissions from database
echo "📋 Getting all permissions from database...\n";
$permissionsOutput = shell_exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="echo json_encode(Spatie\Permission\Models\Permission::pluck(\'name\')->toArray());" 2>/dev/null');

$permissions = json_decode(trim($permissionsOutput), true) ?: [];
$totalPermissions = count($permissions);
echo "✅ Found $totalPermissions permissions\n\n";

// Group routes by module
$routesByModule = [];
foreach ($routes as $route) {
    $module = $route['module'];
    if (!isset($routesByModule[$module])) {
        $routesByModule[$module] = [];
    }
    $routesByModule[$module][] = $route;
}

echo "📊 MODULES FOUND:\n";
echo str_repeat("-", 60) . "\n";
foreach (array_keys($routesByModule) as $module) {
    $count = count($routesByModule[$module]);
    echo sprintf("  %-30s : %d routes\n", ucfirst($module), $count);
}
echo "\n";

// Check which modules have permissions
echo "🔍 PERMISSION COVERAGE BY MODULE:\n";
echo str_repeat("-", 60) . "\n";

$missingPermissions = [];
$coveredModules = [];
$uncoveredModules = [];

foreach (array_keys($routesByModule) as $module) {
    $hasPermission = false;
    foreach ($permissions as $permission) {
        if (str_starts_with($permission, "$module.")) {
            $hasPermission = true;
            break;
        }
    }

    if ($hasPermission) {
        $coveredModules[] = $module;
        echo "  ✅ " . ucfirst($module) . "\n";
    } else {
        $uncoveredModules[] = $module;
        echo "  ❌ " . ucfirst($module) . " - NO PERMISSIONS FOUND\n";
        $missingPermissions[] = $module;
    }
}

echo "\n";
echo "📊 SUMMARY:\n";
echo str_repeat("-", 60) . "\n";
echo "Total Modules: " . count(array_keys($routesByModule)) . "\n";
echo "Covered Modules: " . count($coveredModules) . " ✅\n";
echo "Uncovered Modules: " . count($uncoveredModules) . " ❌\n";
echo "\n";

if (!empty($missingPermissions)) {
    echo "⚠️  MISSING PERMISSIONS FOR MODULES:\n";
    echo str_repeat("-", 60) . "\n";
    foreach ($missingPermissions as $module) {
        echo "  • $module\n";
        echo "    Suggested permissions:\n";
        echo "    - $module.view\n";
        echo "    - $module.create\n";
        echo "    - $module.edit\n";
        echo "    - $module.delete\n";
        echo "\n";
    }
}

echo "\n";
echo "✅ Permission coverage check complete!\n";
