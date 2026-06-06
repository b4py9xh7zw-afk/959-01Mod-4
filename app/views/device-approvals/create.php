<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">提交设备指纹变更申请</h1>
        <p class="text-gray-600">更换主板、迁移服务器或重装系统后，请提交以下信息申请变更设备绑定。</p>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>注意：</strong>提交后需要客服审核通过才能释放原设备绑定。短时间内频繁申请变更（30天内超过3次）将进入风险复核流程。
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="license_id" class="block text-sm font-medium text-gray-700 mb-2">
                    选择许可证 <span class="text-red-500">*</span>
                </label>
                <select id="license_id" name="license_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    onchange="updateOldFingerprint()">
                    <option value="">请选择要变更的许可证</option>
                    <?php 
                        $selectedLicenseId = isset($_GET['license_id']) ? (int)$_GET['license_id'] : 0;
                        $preSelectedFingerprint = '';
                    ?>
                    <?php foreach ($licenses as $license): ?>
                        <?php 
                            $bindings = (new DeviceBinding())->findByLicenseId($license['id']);
                            $hasBinding = !empty($bindings);
                            $currentFingerprint = $hasBinding ? $bindings[0]['device_fingerprint'] : '';
                            $isSelected = $selectedLicenseId === (int)$license['id'] && $hasBinding;
                            if ($isSelected) {
                                $preSelectedFingerprint = $currentFingerprint;
                            }
                        ?>
                        <option value="<?php echo $license['id']; ?>" 
                            data-fingerprint="<?php echo htmlspecialchars($currentFingerprint); ?>"
                            <?php echo !$hasBinding ? 'disabled' : ''; ?>
                            <?php echo $isSelected ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($license['license_key']); ?> - <?php echo htmlspecialchars($license['product_name']); ?>
                            <?php echo !$hasBinding ? '(未绑定设备)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">只显示已绑定设备的许可证</p>
            </div>

            <div>
                <label for="old_fingerprint" class="block text-sm font-medium text-gray-700 mb-2">
                    旧设备指纹 <span class="text-red-500">*</span>
                </label>
                <input type="text" id="old_fingerprint" name="old_fingerprint" required
                    placeholder="请输入或选择许可证后自动填充"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm">
                <p class="mt-1 text-sm text-gray-500">当前绑定的设备指纹，选择许可证后自动填充</p>
            </div>

            <div>
                <label for="new_fingerprint" class="block text-sm font-medium text-gray-700 mb-2">
                    新设备指纹 <span class="text-red-500">*</span>
                </label>
                <input type="text" id="new_fingerprint" name="new_fingerprint" required
                    placeholder="请输入新设备的指纹"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm">
                <p class="mt-1 text-sm text-gray-500">新设备的硬件指纹，通常由客户端软件自动生成</p>
            </div>

            <div>
                <label for="change_reason" class="block text-sm font-medium text-gray-700 mb-2">
                    变更原因 <span class="text-red-500">*</span>
                </label>
                <textarea id="change_reason" name="change_reason" rows="4" required
                    placeholder="请详细说明变更原因，如：更换主板、服务器迁移、重装系统等"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                <p class="mt-1 text-sm text-gray-500">详细的原因描述有助于加快审核速度</p>
            </div>

            <div>
                <label for="screenshot" class="block text-sm font-medium text-gray-700 mb-2">
                    相关截图
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="screenshot" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>上传截图</span>
                                <input id="screenshot" name="screenshot" type="file" accept="image/*" class="sr-only" onchange="updateFileName(this)">
                            </label>
                            <p class="pl-1">或拖放文件</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF, WebP 格式，最大 5MB</p>
                        <p id="file-name" class="text-sm text-green-600 hidden"></p>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">可选，提供设备信息截图或系统信息截图有助于审核</p>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="/device-approvals" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    取消
                </a>
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    提交申请
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">常见变更原因说明</h3>
        <div class="space-y-3">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">更换主板</p>
                    <p class="text-sm text-gray-500">电脑主板损坏或升级导致硬件指纹变更</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">服务器迁移</p>
                    <p class="text-sm text-gray-500">将软件部署到新的服务器环境</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">重装系统</p>
                    <p class="text-sm text-gray-500">操作系统重装导致部分硬件标识变更</p>
                </div>
            </div>
            <div class="flex items-start">
                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">更换设备</p>
                    <p class="text-sm text-gray-500">更换新电脑或新设备需要重新激活</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateOldFingerprint() {
    const select = document.getElementById('license_id');
    const selectedOption = select.options[select.selectedIndex];
    const fingerprint = selectedOption.getAttribute('data-fingerprint');
    document.getElementById('old_fingerprint').value = fingerprint || '';
}

function updateFileName(input) {
    const fileName = input.files[0]?.name;
    const fileNameElement = document.getElementById('file-name');
    if (fileName) {
        fileNameElement.textContent = '已选择: ' + fileName;
        fileNameElement.classList.remove('hidden');
    } else {
        fileNameElement.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('license_id');
    if (select.value) {
        updateOldFingerprint();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
