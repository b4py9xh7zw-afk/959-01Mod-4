<?php
/**
 * DeviceFingerprintApproval Model - Manages device fingerprint change approval workflow
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/DeviceBinding.php';

class DeviceFingerprintApproval {
    private $db;
    private $deviceBinding;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->deviceBinding = new DeviceBinding();
    }
    
    const FREQUENT_CHANGE_THRESHOLD = 3;
    const FREQUENT_CHANGE_DAYS = 30;
    
    public function create($data) {
        $riskLevel = $this->calculateRiskLevel($data['user_id'], $data['license_id']);
        $status = $riskLevel === 'high' ? 'risk_review' : 'pending';
        
        $sql = "INSERT INTO device_fingerprint_approvals 
                (user_id, license_id, old_fingerprint, new_fingerprint, change_reason, screenshot_path, status, risk_level) 
                VALUES (:user_id, :license_id, :old_fingerprint, :new_fingerprint, :change_reason, :screenshot_path, :status, :risk_level)";
        
        $params = [
            ':user_id' => $data['user_id'],
            ':license_id' => $data['license_id'],
            ':old_fingerprint' => $data['old_fingerprint'],
            ':new_fingerprint' => $data['new_fingerprint'],
            ':change_reason' => $data['change_reason'],
            ':screenshot_path' => $data['screenshot_path'] ?? null,
            ':status' => $status,
            ':risk_level' => $riskLevel
        ];
        
        $this->db->execute($sql, $params);
        $approvalId = $this->db->lastInsertId();
        
        return [
            'id' => $approvalId,
            'status' => $status,
            'risk_level' => $riskLevel
        ];
    }
    
    public function calculateRiskLevel($userId, $licenseId) {
        $sql = "SELECT COUNT(*) as count FROM device_fingerprint_approvals 
                WHERE user_id = :user_id AND license_id = :license_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::FREQUENT_CHANGE_DAYS . " DAY)
                AND status IN ('approved', 'pending', 'risk_review')";
        
        $result = $this->db->fetchOne($sql, [
            ':user_id' => $userId,
            ':license_id' => $licenseId
        ]);
        
        $recentChanges = $result['count'] ?? 0;
        
        if ($recentChanges >= self::FREQUENT_CHANGE_THRESHOLD) {
            return 'high';
        } elseif ($recentChanges >= 1) {
            return 'medium';
        }
        
        return 'low';
    }
    
    public function getRecentChangeCount($userId, $licenseId) {
        $sql = "SELECT COUNT(*) as count FROM device_fingerprint_approvals 
                WHERE user_id = :user_id AND license_id = :license_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::FREQUENT_CHANGE_DAYS . " DAY)
                AND status IN ('approved', 'pending', 'risk_review')";
        
        $result = $this->db->fetchOne($sql, [
            ':user_id' => $userId,
            ':license_id' => $licenseId
        ]);
        
        return $result['count'] ?? 0;
    }
    
    public function findById($id) {
        $sql = "SELECT dfa.*, l.license_key, l.product_name, 
                u.username as applicant_name, u.email as applicant_email,
                r.username as reviewer_name
                FROM device_fingerprint_approvals dfa
                LEFT JOIN licenses l ON dfa.license_id = l.id
                LEFT JOIN users u ON dfa.user_id = u.id
                LEFT JOIN users r ON dfa.reviewer_id = r.id
                WHERE dfa.id = :id";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function findByUserId($userId, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        
        $sql = "SELECT dfa.*, l.license_key, l.product_name,
                r.username as reviewer_name
                FROM device_fingerprint_approvals dfa
                LEFT JOIN licenses l ON dfa.license_id = l.id
                LEFT JOIN users r ON dfa.reviewer_id = r.id
                WHERE dfa.user_id = :user_id
                ORDER BY dfa.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    public function findAll($status = null, $riskLevel = null, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        
        $where = [];
        $params = [];
        
        if ($status) {
            $where[] = "dfa.status = :status";
            $params[':status'] = $status;
        }
        if ($riskLevel) {
            $where[] = "dfa.risk_level = :risk_level";
            $params[':risk_level'] = $riskLevel;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT dfa.*, l.license_key, l.product_name,
                u.username as applicant_name, u.email as applicant_email,
                r.username as reviewer_name
                FROM device_fingerprint_approvals dfa
                LEFT JOIN licenses l ON dfa.license_id = l.id
                LEFT JOIN users u ON dfa.user_id = u.id
                LEFT JOIN users r ON dfa.reviewer_id = r.id
                {$whereClause}
                ORDER BY dfa.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function count($status = null, $riskLevel = null) {
        $where = [];
        $params = [];
        
        if ($status) {
            $where[] = "status = :status";
            $params[':status'] = $status;
        }
        if ($riskLevel) {
            $where[] = "risk_level = :risk_level";
            $params[':risk_level'] = $riskLevel;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT COUNT(*) as count FROM device_fingerprint_approvals {$whereClause}";
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    public function hasPendingApproval($licenseId) {
        $sql = "SELECT COUNT(*) as count FROM device_fingerprint_approvals 
                WHERE license_id = :license_id AND status IN ('pending', 'risk_review')";
        $result = $this->db->fetchOne($sql, [':license_id' => $licenseId]);
        return ($result['count'] ?? 0) > 0;
    }
    
    public function approve($id, $reviewerId, $reviewNotes = null) {
        $approval = $this->findById($id);
        if (!$approval) {
            throw new Exception('审批记录不存在');
        }
        
        if ($approval['status'] === 'approved' || $approval['status'] === 'rejected') {
            throw new Exception('该审批已处理，无法重复操作');
        }
        
        try {
            $this->db->getConnection()->beginTransaction();
            
            $sql = "UPDATE device_fingerprint_approvals 
                    SET status = 'approved', review_notes = :review_notes, 
                        reviewer_id = :reviewer_id, reviewed_at = NOW()
                    WHERE id = :id";
            
            $this->db->execute($sql, [
                ':id' => $id,
                ':review_notes' => $reviewNotes,
                ':reviewer_id' => $reviewerId
            ]);
            
            $this->deviceBinding->deleteByLicenseAndFingerprint(
                $approval['license_id'],
                $approval['old_fingerprint']
            );
            
            $this->banDevice(
                $approval['license_id'],
                $approval['old_fingerprint'],
                $id,
                '设备指纹变更审批通过，旧设备已禁用',
                $reviewerId
            );
            
            $this->db->getConnection()->commit();
            return true;
        } catch (Exception $e) {
            $this->db->getConnection()->rollBack();
            throw $e;
        }
    }
    
    public function reject($id, $reviewerId, $reviewNotes) {
        $approval = $this->findById($id);
        if (!$approval) {
            throw new Exception('审批记录不存在');
        }
        
        if ($approval['status'] === 'approved' || $approval['status'] === 'rejected') {
            throw new Exception('该审批已处理，无法重复操作');
        }
        
        if (empty($reviewNotes)) {
            throw new Exception('拒绝审批必须填写审核备注');
        }
        
        $sql = "UPDATE device_fingerprint_approvals 
                SET status = 'rejected', review_notes = :review_notes, 
                    reviewer_id = :reviewer_id, reviewed_at = NOW()
                WHERE id = :id";
        
        $this->db->execute($sql, [
            ':id' => $id,
            ':review_notes' => $reviewNotes,
            ':reviewer_id' => $reviewerId
        ]);
        
        return true;
    }
    
    public function requestRiskReview($id, $reviewerId) {
        $sql = "UPDATE device_fingerprint_approvals 
                SET status = 'risk_review', reviewer_id = :reviewer_id
                WHERE id = :id AND status = 'pending'";
        
        $this->db->execute($sql, [
            ':id' => $id,
            ':reviewer_id' => $reviewerId
        ]);
        
        return true;
    }
    
    private function banDevice($licenseId, $deviceFingerprint, $approvalId, $reason, $bannedBy) {
        $sql = "INSERT INTO banned_devices (license_id, device_fingerprint, approval_id, ban_reason, banned_by)
                VALUES (:license_id, :device_fingerprint, :approval_id, :ban_reason, :banned_by)
                ON DUPLICATE KEY UPDATE 
                    ban_reason = :ban_reason2, 
                    banned_by = :banned_by2,
                    created_at = NOW()";
        
        $this->db->execute($sql, [
            ':license_id' => $licenseId,
            ':device_fingerprint' => $deviceFingerprint,
            ':approval_id' => $approvalId,
            ':ban_reason' => $reason,
            ':banned_by' => $bannedBy,
            ':ban_reason2' => $reason,
            ':banned_by2' => $bannedBy
        ]);
    }
    
    public function getBannedDevices($licenseId = null, $limit = 100, $offset = 0) {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        
        $where = [];
        $params = [];
        
        if ($licenseId) {
            $where[] = "bd.license_id = :license_id";
            $params[':license_id'] = $licenseId;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT bd.*, l.license_key, l.product_name,
                u.username as banned_by_name
                FROM banned_devices bd
                LEFT JOIN licenses l ON bd.license_id = l.id
                LEFT JOIN users u ON bd.banned_by = u.id
                {$whereClause}
                ORDER BY bd.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->db->fetchAll($sql, $params);
    }
}
