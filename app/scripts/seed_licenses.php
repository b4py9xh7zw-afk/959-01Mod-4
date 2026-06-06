<?php
/**
 * Seed Licenses Script
 * This script creates sample licenses after users are created
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/License.php';
require_once __DIR__ . '/../models/DeviceBinding.php';

try {
    $userModel = new User();
    $licenseModel = new License();
    $deviceBinding = new DeviceBinding();
    
    // Get admin user
    $admin = $userModel->findByEmail('admin@license-platform.com');
    if (!$admin) {
        echo "Admin user not found. Please run seed_users.php first.\n";
        exit(1);
    }
    
    // Get test user
    $testUser = $userModel->findByEmail('user@license-platform.com');
    if (!$testUser) {
        echo "Test user not found. Please run seed_users.php first.\n";
        exit(1);
    }
    
    // Check if licenses already exist
    $existingLicenses = $licenseModel->findAll(10, 0);
    $licensesCreated = false;
    
    if (count($existingLicenses) === 0) {
        // Create sample licenses for admin
        $licenseModel->create([
            'user_id' => $admin['id'],
            'product_name' => 'Premium Software License',
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 year'))
        ]);
        
        $licenseModel->create([
            'user_id' => $admin['id'],
            'product_name' => 'Enterprise License',
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+2 years'))
        ]);
        
        // Create sample licenses for test user
        $licenseModel->create([
            'user_id' => $testUser['id'],
            'product_name' => 'Basic License',
            'status' => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+6 months'))
        ]);
        
        $licenseModel->create([
            'user_id' => $testUser['id'],
            'product_name' => 'Trial License',
            'status' => 'expired',
            'expires_at' => date('Y-m-d H:i:s', strtotime('-1 month'))
        ]);
        
        $licensesCreated = true;
        echo "Sample licenses created successfully!\n";
    } else {
        echo "Licenses already exist.\n";
    }
    
    // Create sample device bindings for demo
    $allLicenses = $licenseModel->findAll(10, 0);
    $bindingCount = 0;
    
    foreach ($allLicenses as $license) {
        $existingBindings = $deviceBinding->findByLicenseId($license['id']);
        if (count($existingBindings) === 0 && $license['status'] === 'active') {
            $fingerprint = md5('demo_device_' . $license['id'] . '_' . time());
            $deviceInfo = json_encode([
                'os' => 'Windows 11 Pro',
                'cpu' => 'Intel Core i7-12700K',
                'mac_address' => '00:1A:2B:3C:4D:5E',
                'hostname' => 'DESKTOP-' . strtoupper(substr(md5(rand()), 0, 7))
            ]);
            
            $deviceBinding->create([
                'license_id' => $license['id'],
                'device_fingerprint' => $fingerprint,
                'device_info' => $deviceInfo
            ]);
            $bindingCount++;
        }
    }
    
    if ($bindingCount > 0) {
        echo "Sample device bindings created: {$bindingCount}\n";
    } else {
        echo "Device bindings already exist.\n";
    }
    
    echo "License seeding completed!\n";
} catch (Exception $e) {
    error_log("License seeding failed: " . $e->getMessage());
    echo "License seeding failed: " . $e->getMessage() . "\n";
    exit(1);
}
