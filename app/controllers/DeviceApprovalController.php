<?php
/**
 * DeviceApprovalController - Manages device fingerprint change approval workflow
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/DeviceFingerprintApproval.php';
require_once __DIR__ . '/../models/DeviceBinding.php';
require_once __DIR__ . '/../models/License.php';

class DeviceApprovalController {
    private $authController;
    private $approvalModel;
    private $deviceBinding;
    private $licenseModel;
    
    public function __construct() {
        $this->authController = new AuthController();
        $this->approvalModel = new DeviceFingerprintApproval();
        $this->deviceBinding = new DeviceBinding();
        $this->licenseModel = new License();
    }
    
    public function index() {
        $this->authController->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $isAdmin = $_SESSION['role'] === 'admin';
        
        if ($isAdmin) {
            $status = $_GET['status'] ?? null;
            $riskLevel = $_GET['risk_level'] ?? null;
            $approvals = $this->approvalModel->findAll($status, $riskLevel);
            $pendingCount = $this->approvalModel->count('pending');
            $riskReviewCount = $this->approvalModel->count('risk_review');
            $approvedCount = $this->approvalModel->count('approved');
            $rejectedCount = $this->approvalModel->count('rejected');
        } else {
            $approvals = $this->approvalModel->findByUserId($userId);
            $pendingCount = 0;
            $riskReviewCount = 0;
            $approvedCount = 0;
            $rejectedCount = 0;
        }
        
        $pageTitle = '设备指纹变更审批';
        require_once __DIR__ . '/../views/device-approvals/index.php';
    }
    
    public function create() {
        $this->authController->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $licenses = $this->licenseModel->findByUserId($userId);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $licenseId = $_POST['license_id'] ?? '';
            $oldFingerprint = $_POST['old_fingerprint'] ?? '';
            $newFingerprint = $_POST['new_fingerprint'] ?? '';
            $changeReason = $_POST['change_reason'] ?? '';
            
            if (empty($licenseId) || empty($oldFingerprint) || empty($newFingerprint) || empty($changeReason)) {
                $_SESSION['error'] = '请填写所有必填字段';
                header('Location: /device-approvals/create');
                exit;
            }
            
            $license = $this->licenseModel->findById($licenseId);
            if (!$license) {
                $_SESSION['error'] = '许可证不存在';
                header('Location: /device-approvals/create');
                exit;
            }
            
            if ($license['user_id'] != $userId) {
                $_SESSION['error'] = '无权操作此许可证';
                header('Location: /device-approvals/create');
                exit;
            }
            
            if ($this->approvalModel->hasPendingApproval($licenseId)) {
                $_SESSION['error'] = '该许可证已有待处理的变更申请，请等待审核完成后再提交';
                header('Location: /device-approvals/create');
                exit;
            }
            
            $existingBinding = $this->deviceBinding->findByLicenseAndFingerprint($licenseId, $oldFingerprint);
            if (!$existingBinding) {
                $_SESSION['error'] = '旧设备指纹与当前绑定的设备指纹不匹配';
                header('Location: /device-approvals/create');
                exit;
            }
            
            if ($oldFingerprint === $newFingerprint) {
                $_SESSION['error'] = '新设备指纹不能与旧设备指纹相同';
                header('Location: /device-approvals/create');
                exit;
            }
            
            $screenshotPath = null;
            if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
                $screenshotPath = $this->handleScreenshotUpload();
                if (!$screenshotPath) {
                    header('Location: /device-approvals/create');
                    exit;
                }
            }
            
            try {
                $result = $this->approvalModel->create([
                    'user_id' => $userId,
                    'license_id' => $licenseId,
                    'old_fingerprint' => $oldFingerprint,
                    'new_fingerprint' => $newFingerprint,
                    'change_reason' => $changeReason,
                    'screenshot_path' => $screenshotPath
                ]);
                
                if ($result['status'] === 'risk_review') {
                    $_SESSION['warning'] = '由于您在短时间内频繁申请变更，您的申请已进入风险复核流程，请耐心等待审核';
                } else {
                    $_SESSION['success'] = '变更申请提交成功，请等待客服审核';
                }
                
                header('Location: /device-approvals/view?id=' . $result['id']);
                exit;
            } catch (Exception $e) {
                error_log("Approval creation error: " . $e->getMessage());
                $_SESSION['error'] = '提交申请失败，请重试';
                header('Location: /device-approvals/create');
                exit;
            }
        }
        
        $pageTitle = '提交设备指纹变更申请';
        require_once __DIR__ . '/../views/device-approvals/create.php';
    }
    
    public function view() {
        $this->authController->requireAuth();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error'] = '审批ID是必填项';
            header('Location: /device-approvals');
            exit;
        }
        
        $approval = $this->approvalModel->findById($id);
        if (!$approval) {
            $_SESSION['error'] = '审批记录不存在';
            header('Location: /device-approvals');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $isAdmin = $_SESSION['role'] === 'admin';
        
        if ($approval['user_id'] != $userId && !$isAdmin) {
            $_SESSION['error'] = '无权查看此审批记录';
            header('Location: /device-approvals');
            exit;
        }
        
        $pageTitle = '审批详情';
        require_once __DIR__ . '/../views/device-approvals/view.php';
    }
    
    public function approve() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /device-approvals');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $reviewNotes = $_POST['review_notes'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = '审批ID是必填项';
            header('Location: /device-approvals');
            exit;
        }
        
        try {
            $this->approvalModel->approve($id, $_SESSION['user_id'], $reviewNotes);
            $_SESSION['success'] = '审批已通过，旧设备绑定已释放并禁用';
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Approval error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        }
    }
    
    public function reject() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /device-approvals');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        $reviewNotes = $_POST['review_notes'] ?? '';
        
        if (!$id) {
            $_SESSION['error'] = '审批ID是必填项';
            header('Location: /device-approvals');
            exit;
        }
        
        if (empty($reviewNotes)) {
            $_SESSION['error'] = '拒绝审批必须填写审核备注';
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        }
        
        try {
            $this->approvalModel->reject($id, $_SESSION['user_id'], $reviewNotes);
            $_SESSION['success'] = '审批已拒绝';
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Rejection error: " . $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        }
    }
    
    public function requestRiskReview() {
        $this->authController->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /device-approvals');
            exit;
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            $_SESSION['error'] = '审批ID是必填项';
            header('Location: /device-approvals');
            exit;
        }
        
        try {
            $this->approvalModel->requestRiskReview($id, $_SESSION['user_id']);
            $_SESSION['success'] = '已提交风险复核';
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        } catch (Exception $e) {
            error_log("Risk review request error: " . $e->getMessage());
            $_SESSION['error'] = '操作失败，请重试';
            header('Location: /device-approvals/view?id=' . $id);
            exit;
        }
    }
    
    public function bannedDevices() {
        $this->authController->requireAdmin();
        
        $licenseId = $_GET['license_id'] ?? null;
        $bannedDevices = $this->approvalModel->getBannedDevices($licenseId);
        
        $pageTitle = '禁用设备列表';
        require_once __DIR__ . '/../views/device-approvals/banned-devices.php';
    }
    
    private function handleScreenshotUpload() {
        $file = $_FILES['screenshot'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = '只支持 JPG、PNG、GIF、WebP 格式的图片';
            return false;
        }
        
        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = '图片大小不能超过 5MB';
            return false;
        }
        
        $uploadDir = __DIR__ . '/../../uploads/screenshots/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'screenshot_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return '/uploads/screenshots/' . $fileName;
        } else {
            $_SESSION['error'] = '文件上传失败，请重试';
            return false;
        }
    }
}
