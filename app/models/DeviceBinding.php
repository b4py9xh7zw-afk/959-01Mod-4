<?php
/**
 * DeviceBinding Model - Manages license to device fingerprint bindings
 */

require_once __DIR__ . '/../config/database.php';

class DeviceBinding {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO device_bindings (license_id, device_fingerprint, device_info, last_activated_at) 
                VALUES (:license_id, :device_fingerprint, :device_info, NOW())";
        
        $params = [
            ':license_id' => $data['license_id'],
            ':device_fingerprint' => $data['device_fingerprint'],
            ':device_info' => $data['device_info'] ?? null
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findById($id) {
        $sql = "SELECT db.*, l.license_key, l.product_name, u.username, u.email 
                FROM device_bindings db
                LEFT JOIN licenses l ON db.license_id = l.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE db.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByLicenseId($licenseId) {
        $sql = "SELECT db.*, l.license_key, l.product_name 
                FROM device_bindings db
                LEFT JOIN licenses l ON db.license_id = l.id
                WHERE db.license_id = :license_id
                ORDER BY db.last_activated_at DESC";
        return $this->db->fetchAll($sql, [':license_id' => $licenseId]);
    }
    
    public function findByFingerprint($deviceFingerprint) {
        $sql = "SELECT db.*, l.license_key, l.product_name, u.username, u.email 
                FROM device_bindings db
                LEFT JOIN licenses l ON db.license_id = l.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE db.device_fingerprint = :device_fingerprint";
        return $this->db->fetchAll($sql, [':device_fingerprint' => $deviceFingerprint]);
    }
    
    public function findByLicenseAndFingerprint($licenseId, $deviceFingerprint) {
        $sql = "SELECT db.*, l.license_key, l.product_name, u.username, u.email 
                FROM device_bindings db
                LEFT JOIN licenses l ON db.license_id = l.id
                LEFT JOIN users u ON l.user_id = u.id
                WHERE db.license_id = :license_id AND db.device_fingerprint = :device_fingerprint";
        return $this->db->fetchOne($sql, [
            ':license_id' => $licenseId,
            ':device_fingerprint' => $deviceFingerprint
        ]);
    }
    
    public function updateLastActivated($id) {
        $sql = "UPDATE device_bindings SET last_activated_at = NOW() WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    public function delete($id) {
        $sql = "DELETE FROM device_bindings WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
        return true;
    }
    
    public function deleteByLicenseAndFingerprint($licenseId, $deviceFingerprint) {
        $sql = "DELETE FROM device_bindings WHERE license_id = :license_id AND device_fingerprint = :device_fingerprint";
        $this->db->execute($sql, [
            ':license_id' => $licenseId,
            ':device_fingerprint' => $deviceFingerprint
        ]);
        return true;
    }
    
    public function countByLicenseId($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM device_bindings WHERE license_id = :license_id";
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        return $result['count'] ?? 0;
    }
    
    public function isFingerprintBanned($licenseId, $deviceFingerprint) {
        $sql = "SELECT COUNT(*) as count FROM banned_devices 
                WHERE license_id = :license_id AND device_fingerprint = :device_fingerprint";
        $result = $this->db->fetchOne($sql, [
            ':license_id' => $licenseId,
            ':device_fingerprint' => $deviceFingerprint
        ]);
        return ($result['count'] ?? 0) > 0;
    }
    
    public function activateLicense($licenseId, $deviceFingerprint, $deviceInfo = null) {
        $binding = $this->findByLicenseAndFingerprint($licenseId, $deviceFingerprint);
        
        if ($this->isFingerprintBanned($licenseId, $deviceFingerprint)) {
            return ['success' => false, 'message' => '该设备已被禁用，无法激活此许可证'];
        }
        
        if ($binding) {
            $this->updateLastActivated($binding['id']);
            return ['success' => true, 'message' => '许可证激活成功', 'binding_id' => $binding['id']];
        }
        
        $currentBindings = $this->countByLicenseId($licenseId);
        $maxBindings = 1;
        
        if ($currentBindings >= $maxBindings) {
            return [
                'success' => false, 
                'message' => '该许可证已绑定到其他设备，如需更换设备请提交设备指纹变更申请'
            ];
        }
        
        $bindingId = $this->create([
            'license_id' => $licenseId,
            'device_fingerprint' => $deviceFingerprint,
            'device_info' => $deviceInfo
        ]);
        
        return ['success' => true, 'message' => '许可证激活成功', 'binding_id' => $bindingId];
    }
}
