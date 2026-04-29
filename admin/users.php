<?php
// 1. Go up one level to find the config folder
require_once(__DIR__ . '/../config/config.php'); 
require_once 'includes/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_balance'])) {
        $user_id = $_POST['user_id'];
        $amount = (float)$_POST['amount'];
        
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);

        $stmt = $db->prepare("INSERT INTO transactions (user_id, amount, type, description) VALUES (?, ?, 'credit', ?)");
        $stmt->execute([$user_id, $amount, "Admin added funds"]);

        $db->commit();
        $message = "Funds added successfully!";
    } elseif (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $role = $_POST['role'];
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        $message = "Role updated!";
    } elseif (isset($_POST['update_password'])) {
        $user_id = $_POST['user_id'];
        $new_password = $_POST['new_password'];
        if (strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $message = "User password updated successfully!";
        } else {
            $message = "Error: Password must be at least 6 characters.";
        }
    } elseif (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'Active') ? 'Blocked' : 'Active';
        $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
        $message = "User status updated to $new_status!";
    }
}

$stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<div class="glass rounded-2xl p-6 md:p-8 shadow-2xl">
    <h2 class="text-2xl font-bold text-white mb-6">Manage Users</h2>

    <?php if ($message): ?>
        <div class="p-4 rounded-lg mb-6 <?php echo strpos($message, 'Error') !== false ? 'bg-red-500/10 border-red-500/50 text-red-400' : 'bg-green-500/10 border-green-500/50 text-green-400'; ?>">
            <i class="fas <?php echo strpos($message, 'Error') !== false ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> mr-2"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-700/50">
                    <th class="pb-3 pr-4 font-black">ID</th>
                    <th class="pb-3 pr-4 font-black">User Info</th>
                    <th class="pb-3 pr-4 font-black">Balance</th>
                    <th class="pb-3 pr-4 font-black">Status</th>
                    <th class="pb-3 pr-4 font-black">Role</th>
                    <th class="pb-3 font-black">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                <?php foreach ($users as $u): ?>
                    <tr class="border-b border-slate-700/30 hover:bg-slate-800/30 transition-colors">
                        <td class="py-4 pr-4 text-slate-500 font-mono">#<?php echo htmlspecialchars($u['id']); ?></td>
                        <td class="py-4 pr-4">
                            <div class="flex flex-col">
                                <span class="text-white font-bold"><?php echo htmlspecialchars($u['name']); ?></span>
                                <span class="text-[10px] text-slate-500"><?php echo htmlspecialchars($u['email']); ?></span>
                            </div>
                        </td>
                        <td class="py-4 pr-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-green-400 font-black">₹<?php echo number_format($u['balance'], 2); ?></span>
                                <button onclick="openBalanceModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>')" class="text-slate-500 hover:text-white transition-colors">
                                    <i class="fas fa-plus-circle text-xs"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-4 pr-4">
                            <form method="POST" action="">
                                <input type="hidden" name="toggle_status" value="1">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <input type="hidden" name="current_status" value="<?php echo $u['status'] ?? 'Active'; ?>">
                                <button type="submit" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-tighter transition-all <?php 
                                    echo ($u['status'] ?? 'Active') === 'Active' ? 'bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20'; 
                                ?>">
                                    <?php echo $u['status'] ?? 'Active'; ?>
                                </button>
                            </form>
                        </td>
                        <td class="py-4 pr-4">
                            <form method="POST" action="" class="flex items-center">
                                <input type="hidden" name="update_role" value="1">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <select name="role" class="bg-slate-800/50 border border-slate-700 rounded-lg px-2 py-1 text-[10px] font-bold text-slate-300 focus:outline-none focus:border-indigo-500" onchange="this.form.submit()">
                                    <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>USER</option>
                                    <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>ADMIN</option>
                                </select>
                            </form>
                        </td>
                        <td class="py-4 space-x-2">
                            <button onclick="openPassModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['name']); ?>')" class="bg-indigo-600/10 hover:bg-indigo-600 text-indigo-400 hover:text-white text-[10px] font-bold px-3 py-1.5 rounded-lg border border-indigo-500/20 transition-all">
                                <i class="fas fa-key mr-1"></i> PASS
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Password Modal -->
<div id="passModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
    <div class="glass border-slate-700 w-full max-w-sm rounded-3xl p-8 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-2">Update Password</h3>
        <p id="passUserName" class="text-xs text-slate-400 mb-8"></p>
        <form method="POST" action="">
            <input type="hidden" name="update_password" value="1">
            <input type="hidden" name="user_id" id="passUserId">
            <div class="mb-6">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">New Password</label>
                <input type="password" name="new_password" required minlength="6" class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 transition-all">
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closePassModal()" class="flex-1 bg-slate-800 text-white font-bold py-3 rounded-xl transition-all">CANCEL</button>
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-indigo-600/20">UPDATE</button>
            </div>
        </form>
    </div>
</div>

<!-- Balance Modal -->
<div id="balanceModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-sm">
    <div class="glass border-slate-700 w-full max-w-sm rounded-3xl p-8 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-2">Add Balance</h3>
        <p id="balanceUserName" class="text-xs text-slate-400 mb-8"></p>
        <form method="POST" action="">
            <input type="hidden" name="add_balance" value="1">
            <input type="hidden" name="user_id" id="balanceUserId">
            <div class="mb-6">
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Amount (₹)</label>
                <input type="number" step="0.01" name="amount" required class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 transition-all">
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeBalanceModal()" class="flex-1 bg-slate-800 text-white font-bold py-3 rounded-xl transition-all">CANCEL</button>
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-green-600/20">ADD FUNDS</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPassModal(id, name) {
    document.getElementById('passUserId').value = id;
    document.getElementById('passUserName').innerText = "Changing password for: " + name;
    document.getElementById('passModal').classList.remove('hidden');
}
function closePassModal() {
    document.getElementById('passModal').classList.add('hidden');
}
function openBalanceModal(id, name) {
    document.getElementById('balanceUserId').value = id;
    document.getElementById('balanceUserName').innerText = "Adding funds to: " + name;
    document.getElementById('balanceModal').classList.remove('hidden');
}
function closeBalanceModal() {
    document.getElementById('balanceModal').classList.add('hidden');
}
</script>

<?php require_once 'includes/footer.php'; ?>

<?php require_once 'includes/footer.php'; ?>
