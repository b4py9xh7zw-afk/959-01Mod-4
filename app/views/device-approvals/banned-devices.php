<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="mb-8">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">禁用设备列表</h1>
        <a href="/device-approvals" class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            返回审批列表
        </a>
    </div>
    <p class="text-gray-600 mt-2">以下设备已被禁用，无法再激活对应许可证</p>
</div>

<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">许可证</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">设备指纹</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">禁用原因</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">关联审批</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作人</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">禁用时间</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($bannedDevices)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            暂无禁用设备
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bannedDevices as $device): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#<?php echo $device['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($device['license_key']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($device['product_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <code class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded"><?php echo htmlspecialchars(substr($device['device_fingerprint'], 0, 20)) . '...'; ?></code>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 max-w-xs truncate" title="<?php echo htmlspecialchars($device['ban_reason']); ?>">
                                    <?php echo htmlspecialchars($device['ban_reason']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($device['approval_id']): ?>
                                    <a href="/device-approvals/view?id=<?php echo $device['approval_id']; ?>" class="text-blue-600 hover:text-blue-900 text-sm">
                                        #<?php echo $device['approval_id']; ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo htmlspecialchars($device['banned_by_name'] ?? '系统'); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('Y-m-d H:i', strtotime($device['created_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8 bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <div class="ml-4">
            <h3 class="text-sm font-medium text-yellow-800">注意事项</h3>
            <div class="mt-2 text-sm text-yellow-700">
                <ul class="list-disc pl-5 space-y-1">
                    <li>被禁用的设备无法再激活对应许可证，即使用户重新安装软件也不行</li>
                    <li>设备禁用以许可证为单位，同一设备指纹可以绑定其他未禁用该指纹的许可证</li>
                    <li>如需解除禁用，请联系技术支持处理</li>
                    <li>所有禁用操作都会记录操作人和关联审批信息，便于审计追踪</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
