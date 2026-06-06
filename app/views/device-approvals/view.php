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
$canReview = $isAdmin && in_array($approval['status'], ['pending', 'risk_review']);
?>

<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="/device-approvals" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors mb-4">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            返回列表
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-3xl font-bold text-gray-800">审批详情 #<?php echo $approval['id']; ?></h1>
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full <?php echo $statusLabels[$approval['status']]['class']; ?>">
                    <?php echo $statusLabels[$approval['status']]['text']; ?>
                </span>
                <span class="px-4 py-2 inline-flex text-sm font-semibold rounded-full <?php echo $riskLabels[$approval['risk_level']]['class']; ?>">
                    <?php echo $riskLabels[$approval['risk_level']]['text']; ?>
                </span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['warning'])): ?>
        <div class="mb-4 bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['warning']); unset($_SESSION['warning']); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    变更信息
                </h2>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">许可证</label>
                            <div class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($approval['license_key']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($approval['product_name']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">申请人</label>
                            <div class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($approval['applicant_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($approval['applicant_email']); ?></div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-500 mb-1">旧设备指纹</label>
                                <code class="text-sm bg-white px-3 py-2 rounded border block overflow-x-auto"><?php echo htmlspecialchars($approval['old_fingerprint']); ?></code>
                            </div>
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-500 mb-1">新设备指纹</label>
                                <code class="text-sm bg-white px-3 py-2 rounded border block overflow-x-auto"><?php echo htmlspecialchars($approval['new_fingerprint']); ?></code>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">变更原因</label>
                        <div class="bg-gray-50 rounded-lg p-4 text-gray-900 whitespace-pre-wrap"><?php echo htmlspecialchars($approval['change_reason']); ?></div>
                    </div>

                    <?php if ($approval['screenshot_path']): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">相关截图</label>
                            <div class="border rounded-lg p-2 bg-gray-50 inline-block">
                                <img src="<?php echo htmlspecialchars($approval['screenshot_path']); ?>" alt="申请截图" class="max-w-full h-auto rounded cursor-pointer" onclick="openImageModal(this.src)">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($approval['review_notes'] || $approval['reviewed_at']): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                        审核信息
                    </h2>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">审核人</label>
                                <div class="text-gray-900"><?php echo htmlspecialchars($approval['reviewer_name'] ?? '系统'); ?></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">审核时间</label>
                                <div class="text-gray-900"><?php echo $approval['reviewed_at'] ? date('Y-m-d H:i:s', strtotime($approval['reviewed_at'])) : '-'; ?></div>
                            </div>
                        </div>
                        <?php if ($approval['review_notes']): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">审核备注</label>
                                <div class="bg-gray-50 rounded-lg p-4 text-gray-900 whitespace-pre-wrap"><?php echo htmlspecialchars($approval['review_notes']); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($canReview): ?>
                <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        审核操作
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">审核备注</label>
                            <textarea id="review_notes_admin" rows="3"
                                placeholder="请填写审核备注（拒绝时必填）"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <?php if ($approval['status'] === 'pending'): ?>
                                <form method="POST" action="/device-approvals/request-risk-review" class="inline">
                                    <input type="hidden" name="id" value="<?php echo $approval['id']; ?>">
                                    <button type="submit" class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center" onclick="return setReviewNotes()">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        提交风险复核
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="/device-approvals/reject" class="inline">
                                <input type="hidden" name="id" value="<?php echo $approval['id']; ?>">
                                <input type="hidden" name="review_notes" id="reject_notes" value="">
                                <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center" onclick="return confirmReject()">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    拒绝申请
                                </button>
                            </form>

                            <form method="POST" action="/device-approvals/approve" class="inline">
                                <input type="hidden" name="id" value="<?php echo $approval['id']; ?>">
                                <input type="hidden" name="review_notes" id="approve_notes" value="">
                                <button type="submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center" onclick="return setApproveNotes()">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    通过申请
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    时间线
                </h2>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-1.5"></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-900">提交申请</p>
                            <p class="text-sm text-gray-500"><?php echo date('Y-m-d H:i:s', strtotime($approval['created_at'])); ?></p>
                        </div>
                    </div>
                    <?php if ($approval['reviewed_at']): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-3 h-3 <?php echo $approval['status'] === 'approved' ? 'bg-green-500' : 'bg-red-500'; ?> rounded-full mt-1.5"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $approval['status'] === 'approved' ? '审核通过' : '审核拒绝'; ?>
                                </p>
                                <p class="text-sm text-gray-500"><?php echo date('Y-m-d H:i:s', strtotime($approval['reviewed_at'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($approval['status'] === 'pending'): ?>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-3 h-3 bg-yellow-500 rounded-full mt-1.5 animate-pulse"></div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">等待审核</p>
                                <p class="text-sm text-gray-500">处理中...</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    审核流程说明
                </h2>
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-xs text-blue-600 font-bold mt-0.5">1</div>
                        <p class="ml-3">客户提交变更申请，包含旧指纹、新指纹、原因和截图</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-xs text-blue-600 font-bold mt-0.5">2</div>
                        <p class="ml-3">系统自动检测风险等级，30天内超过3次变更自动进入风险复核</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-xs text-blue-600 font-bold mt-0.5">3</div>
                        <p class="ml-3">客服审核申请，可选择通过、拒绝或提交风险复核</p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center text-xs text-blue-600 font-bold mt-0.5">4</div>
                        <p class="ml-3">审核通过后，旧设备绑定自动释放并禁用，新设备可激活</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50" onclick="closeImageModal()">
    <div class="max-w-4xl max-h-screen p-4">
        <img id="modalImage" src="" alt="Full size screenshot" class="max-w-full max-h-full object-contain rounded-lg">
    </div>
    <button class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300" onclick="closeImageModal()">&times;</button>
</div>

<script>
function setReviewNotes() {
    const notes = document.getElementById('review_notes_admin').value;
    return true;
}

function confirmReject() {
    const notes = document.getElementById('review_notes_admin').value.trim();
    if (!notes) {
        alert('拒绝申请必须填写审核备注');
        return false;
    }
    document.getElementById('reject_notes').value = notes;
    return confirm('确定要拒绝此申请吗？');
}

function setApproveNotes() {
    const notes = document.getElementById('review_notes_admin').value;
    document.getElementById('approve_notes').value = notes;
    return confirm('确定要通过此申请吗？通过后旧设备将被禁用，无法再激活此许可证。');
}

function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = src;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
