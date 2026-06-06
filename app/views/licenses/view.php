<?php $pageTitle = '许可证详情 - 许可证管理平台';
require_once __DIR__ . '/../layouts/header.php';

$isOwner = $license['user_id'] == $_SESSION['user_id'];
$isAdmin = $_SESSION['role'] === 'admin';
$canActivate = $license['status'] === 'active' && ($isOwner || $isAdmin);
$hasBindings = !empty($bindings);
?>

<div class="max-w-4xl mx-auto space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            许可证详情
        </h1>
        <a href="/dashboard/licenses" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
            ← 返回许可证列表
        </a>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-8">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">许可证密钥</label>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <code class="text-lg font-mono text-gray-800"><?php echo htmlspecialchars($license['license_key']); ?></code>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">状态</label>
                    <div class="mt-2">
                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full <?php 
                            echo $license['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                ($license['status'] === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'); 
                        ?>">
                            <?php 
                            echo $license['status'] === 'active' ? '活跃' : 
                                ($license['status'] === 'expired' ? '已过期' : '未激活'); 
                            ?>
                        </span>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">产品名称</label>
                    <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['product_name']); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">分配用户</label>
                    <p class="text-lg text-gray-800"><?php echo htmlspecialchars($license['username'] ?? 'N/A'); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($license['email'] ?? ''); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">创建时间</label>
                    <p class="text-lg text-gray-800"><?php echo date('Y-m-d H:i:s', strtotime($license['created_at'])); ?></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-2">过期时间</label>
                    <p class="text-lg text-gray-800">
                        <?php echo $license['expires_at'] ? date('Y-m-d H:i:s', strtotime($license['expires_at'])) : '永不过期'; ?>
                    </p>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-6 mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">设备绑定信息</h3>
                    <div class="flex items-center gap-2">
                        <?php if ($hasBindings): ?>
                            <a href="/device-approvals/create" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors text-sm">
                                申请变更设备
                            </a>
                        <?php endif; ?>
                        <?php if ($canActivate && !$hasBindings): ?>
                            <button 
                                onclick="document.getElementById('activateForm').classList.toggle('hidden')"
                                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                            >
                                模拟激活设备
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($canActivate && !$hasBindings): ?>
                <form id="activateForm" method="POST" action="/licenses/activate" class="hidden mb-4 bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                    <div class="mb-3">
                        <label for="device_fingerprint" class="block text-sm font-medium text-gray-700 mb-1">设备指纹（可选，留空将自动生成）</label>
                        <input 
                            type="text" 
                            id="device_fingerprint" 
                            name="device_fingerprint" 
                            placeholder="输入自定义设备指纹，或留空自动生成"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm"
                        >
                    </div>
                    <div class="flex gap-3">
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
                            onclick="return confirm('确定要模拟激活此设备吗？')"
                        >
                            确认激活
                        </button>
                        <button 
                            type="button"
                            onclick="document.getElementById('activateForm').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm"
                        >
                            取消
                        </button>
                    </div>
                </form>
                <?php endif; ?>
                
                <?php if ($hasBindings): ?>
                    <div class="bg-gray-50 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">设备指纹</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">设备信息</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最后激活时间</th>
                                    <?php if ($isAdmin): ?>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($bindings as $binding): ?>
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <code class="text-xs bg-gray-100 px-2 py-1 rounded block max-w-xs truncate" title="<?php echo htmlspecialchars($binding['device_fingerprint']); ?>">
                                            <?php echo htmlspecialchars($binding['device_fingerprint']); ?>
                                        </code>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php 
                                            $deviceInfo = json_decode($binding['device_info'], true);
                                            if ($deviceInfo) {
                                                echo '<div class="text-sm text-gray-700">';
                                                foreach ($deviceInfo as $key => $value) {
                                                    echo '<div><span class="text-gray-500">' . htmlspecialchars($key) . ':</span> ' . htmlspecialchars($value) . '</div>';
                                                }
                                                echo '</div>';
                                            } else {
                                                echo '<span class="text-sm text-gray-500">-</span>';
                                            }
                                        ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo $binding['last_activated_at'] ? date('Y-m-d H:i:s', strtotime($binding['last_activated_at'])) : '-'; ?>
                                    </td>
                                    <?php if ($isAdmin): ?>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <form method="POST" action="/licenses/deactivate" onsubmit="return confirm('确定要解除此设备绑定吗？普通用户需要通过变更申请流程。');" class="inline">
                                            <input type="hidden" name="binding_id" value="<?php echo $binding['id']; ?>">
                                            <input type="hidden" name="license_id" value="<?php echo $license['id']; ?>">
                                            <button 
                                                type="submit"
                                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors text-xs"
                                            >
                                                解除绑定
                                            </button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-yellow-800 font-medium">设备更换提示</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    每个许可证只能绑定一台设备。如需更换设备，请
                                    <a href="/device-approvals/create" class="text-blue-600 hover:text-blue-800 underline">提交设备指纹变更申请</a>，
                                    经客服审核通过后才能解绑旧设备并激活新设备。
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-lg p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 mb-2">此许可证尚未绑定任何设备</p>
                        <?php if ($canActivate): ?>
                            <p class="text-sm text-gray-400">点击上方"模拟激活设备"按钮进行演示</p>
                        <?php else: ?>
                            <p class="text-sm text-gray-400">许可证未激活，无法绑定设备</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($isAdmin): ?>
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">管理员操作</h3>
                <div class="flex flex-wrap gap-3">
                    <button 
                        onclick="document.getElementById('updateForm').classList.toggle('hidden')"
                        class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        编辑许可证
                    </button>
                    <form method="POST" action="/licenses/delete" onsubmit="return confirm('确定要删除此许可证吗？');" class="inline">
                        <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
                        >
                            删除许可证
                        </button>
                    </form>
                </div>
                
                <form id="updateForm" method="POST" action="/licenses/update" class="hidden mt-6 space-y-4 bg-gray-50 p-6 rounded-lg">
                    <input type="hidden" name="id" value="<?php echo $license['id']; ?>">
                    
                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">产品名称</label>
                        <input 
                            type="text" 
                            id="product_name" 
                            name="product_name" 
                            value="<?php echo htmlspecialchars($license['product_name']); ?>"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">状态</label>
                        <select 
                            id="status" 
                            name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="active" <?php echo $license['status'] === 'active' ? 'selected' : ''; ?>>活跃</option>
                            <option value="inactive" <?php echo $license['status'] === 'inactive' ? 'selected' : ''; ?>>未激活</option>
                            <option value="expired" <?php echo $license['status'] === 'expired' ? 'selected' : ''; ?>>已过期</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">过期时间</label>
                        <input 
                            type="date" 
                            id="expires_at" 
                            name="expires_at"
                            value="<?php echo $license['expires_at'] ? date('Y-m-d', strtotime($license['expires_at'])) : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                    </div>
                    
                    <div class="flex space-x-4">
                        <button 
                            type="submit"
                            class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            更新许可证
                        </button>
                        <button 
                            type="button"
                            onclick="document.getElementById('updateForm').classList.add('hidden')"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            取消
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
