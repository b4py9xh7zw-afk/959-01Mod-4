<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
$isAdmin = $_SESSION['role'] === 'admin';
$statusLabels = [
    'pending' => ['text' => '待审核', 'class' => 'bg-yellow-100 text-yellow-800'],
    'risk_review' => ['text' => '风险复核', 'class' => 'bg-orange-100 text-orange-800'],
    'approved' => ['text' => '已通过', 'class' => 'bg-green-100 text-green-800'],
    'rejected' => ['text' => '已拒绝', 'class' => 'bg-red-100 text-red-800']
];
$riskLabels = [
    'low' => ['text' => '低风险', 'class' => 'bg-green-100 text-green-800'],
    'medium' => ['text' => '中风险', 'class' => 'bg-yellow-100 text-yellow-800'],
    'high' => ['text' => '高风险', 'class' => 'bg-red-100 text-red-800']
];
?>
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-3xl font-bold text-gray-800">设备指纹变更审批</h1>
        <?php if (!$isAdmin): ?>
            <a href="/device-approvals/create" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                提交变更申请
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">待审核</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $pendingCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">风险复核</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $riskReviewCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">已通过</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $approvedCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">已拒绝</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo $rejectedCount; ?></p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">状态筛选</label>
            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">全部状态</option>
                <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>待审核</option>
                <option value="risk_review" <?php echo (isset($_GET['status']) && $_GET['status'] === 'risk_review') ? 'selected' : ''; ?>>风险复核</option>
                <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'selected' : ''; ?>>已通过</option>
                <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] === 'rejected') ? 'selected' : ''; ?>>已拒绝</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">风险等级</label>
            <select name="risk_level" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">全部等级</option>
                <option value="low" <?php echo (isset($_GET['risk_level']) && $_GET['risk_level'] === 'low') ? 'selected' : ''; ?>>低风险</option>
                <option value="medium" <?php echo (isset($_GET['risk_level']) && $_GET['risk_level'] === 'medium') ? 'selected' : ''; ?>>中风险</option>
                <option value="high" <?php echo (isset($_GET['risk_level']) && $_GET['risk_level'] === 'high') ? 'selected' : ''; ?>>高风险</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            筛选
        </button>
        <a href="/device-approvals" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            重置
        </a>
        <a href="/device-approvals/banned-devices" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors ml-auto">
            禁用设备列表
        </a>
    </form>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <?php if ($isAdmin): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">申请人</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">旧指纹</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">新指纹</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">风险等级</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">申请时间</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($approvals)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            暂无审批记录
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($approvals as $approval): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $approval['id']; ?></td>
                            <?php if ($isAdmin): ?>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($approval['applicant_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($approval['applicant_email']); ?></div>
                                </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($approval['license_key']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($approval['product_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars(substr($approval['old_fingerprint'], 0, 16)) . '...'; ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars(substr($approval['new_fingerprint'], 0, 16)) . '...'; ?></code>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusLabels[$approval['status']]['class']; ?>">
                                    <?php echo $statusLabels[$approval['status']]['text']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $riskLabels[$approval['risk_level']]['class']; ?>">
                                    <?php echo $riskLabels[$approval['risk_level']]['text']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('Y-m-d H:i', strtotime($approval['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="/device-approvals/view?id=<?php echo $approval['id']; ?>" class="text-blue-600 hover:text-blue-900 transition-colors">
                                    查看详情
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
