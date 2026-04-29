<?php
// profile.php
require_once('config/config.php');
require_once('includes/header.php');

$message = '';
$messageType = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // We need to fetch the latest user data including password hash
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch();

    if (password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user['id']])) {
                    $_SESSION['flash_message'] = 'Password updated successfully!';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Failed to update password.';
                    $_SESSION['flash_type'] = 'error';
                }
            } else {
                $_SESSION['flash_message'] = 'New password must be at least 6 characters.';
                $_SESSION['flash_type'] = 'error';
            }
        } else {
            $_SESSION['flash_message'] = 'New passwords do not match.';
            $_SESSION['flash_type'] = 'error';
        }
    } else {
        $_SESSION['flash_message'] = 'Current password is incorrect.';
        $_SESSION['flash_type'] = 'error';
    }
    header("Location: profile.php");
    exit;
}

$message = $_SESSION['flash_message'] ?? '';
$messageType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Fetch Stats
$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$total_orders = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = 'Completed'");
$stmt->execute([$user['id']]);
$success_orders = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ? AND status = 'Pending'");
$stmt->execute([$user['id']]);
$pending_orders = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT SUM(charge) as total FROM orders WHERE user_id = ?");
$stmt->execute([$user['id']]);
$total_spent = $stmt->fetch()['total'] ?? 0;
?>

<div class="max-w-5xl mx-auto px-4">
    <!-- Header Section -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-4xl font-black text-white tracking-tight">User Dashboard</h2>
            <p class="text-slate-400 mt-2 font-medium flex items-center">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                Welcome back, <?php echo htmlspecialchars($user['name']); ?>
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?php echo BASE_URL; ?>/index" class="bg-indigo-600/10 hover:bg-indigo-600/20 text-indigo-400 px-6 py-3 rounded-2xl font-bold transition-all flex items-center border border-indigo-600/20">
                <i class="fas fa-plus-circle mr-2"></i> New Order
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
        <!-- Total Orders -->
        <div class="glass-card rounded-[2rem] p-7 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-indigo-600/10 rounded-full blur-3xl group-hover:bg-indigo-600/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-indigo-600/20 text-indigo-400 rounded-2xl flex items-center justify-center mb-5 shadow-inner">
                    <i class="fas fa-shopping-bag text-2xl"></i>
                </div>
                <p class="text-[11px] text-slate-500 uppercase font-black tracking-[0.2em] mb-1">Total Orders</p>
                <h3 class="text-3xl font-black text-white tracking-tight"><?php echo number_format($total_orders); ?></h3>
            </div>
        </div>

        <!-- Success Orders -->
        <div class="glass-card rounded-[2rem] p-7 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-600/10 rounded-full blur-3xl group-hover:bg-emerald-600/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-emerald-600/20 text-emerald-400 rounded-2xl flex items-center justify-center mb-5 shadow-inner">
                    <i class="fas fa-check-double text-2xl"></i>
                </div>
                <p class="text-[11px] text-slate-500 uppercase font-black tracking-[0.2em] mb-1">Completed</p>
                <h3 class="text-3xl font-black text-white tracking-tight"><?php echo number_format($success_orders); ?></h3>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="glass-card rounded-[2rem] p-7 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-amber-600/10 rounded-full blur-3xl group-hover:bg-amber-600/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-amber-600/20 text-amber-400 rounded-2xl flex items-center justify-center mb-5 shadow-inner">
                    <i class="fas fa-hourglass-half text-2xl"></i>
                </div>
                <p class="text-[11px] text-slate-500 uppercase font-black tracking-[0.2em] mb-1">Pending</p>
                <h3 class="text-3xl font-black text-white tracking-tight"><?php echo number_format($pending_orders); ?></h3>
            </div>
        </div>

        <!-- Total Spent -->
        <div class="glass-card rounded-[2rem] p-7 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-purple-600/10 rounded-full blur-3xl group-hover:bg-purple-600/20 transition-all duration-500"></div>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-purple-600/20 text-purple-400 rounded-2xl flex items-center justify-center mb-5 shadow-inner">
                    <i class="fas fa-coins text-2xl"></i>
                </div>
                <p class="text-[11px] text-slate-500 uppercase font-black tracking-[0.2em] mb-1">Total Spent</p>
                <h3 class="text-3xl font-black text-white tracking-tight">₹<?php echo number_format($total_spent, 2); ?></h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Account Information -->
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-card rounded-[2.5rem] p-8 border border-white/5">
                <div class="flex items-center space-x-4 mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl flex items-center justify-center text-white text-2xl font-black shadow-xl">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-lg"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-slate-500 text-xs font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="p-5 rounded-3xl bg-slate-900/50 border border-slate-800/50">
                        <p class="text-[10px] text-slate-500 uppercase font-black tracking-widest mb-1">Current Balance</p>
                        <p class="text-2xl font-black text-white">₹<?php echo number_format($user['balance'], 2); ?></p>
                    </div>
                    
                    <div class="flex items-center justify-between px-2">
                        <span class="text-sm text-slate-400 font-medium">Account Status</span>
                        <span class="px-3 py-1 bg-green-500/10 text-green-400 text-[10px] font-black uppercase rounded-lg border border-green-500/20">Verified</span>
                    </div>
                    
                    <div class="flex items-center justify-between px-2">
                        <span class="text-sm text-slate-400 font-medium">Member Since</span>
                        <span class="text-sm text-white font-bold"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>

                <div class="mt-10">
                    <a href="<?php echo BASE_URL; ?>/logout" class="w-full flex items-center justify-center py-4 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-2xl font-bold transition-all border border-red-500/20 text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i> Sign Out
                    </a>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-[2.5rem] p-10 border border-white/5 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-10 opacity-5 pointer-events-none">
                    <i class="fas fa-shield-alt text-9xl text-white"></i>
                </div>

                <h3 class="text-2xl font-black text-white mb-8 flex items-center">
                    <span class="w-10 h-10 bg-indigo-600/20 text-indigo-400 rounded-xl flex items-center justify-center mr-4 shadow-inner">
                        <i class="fas fa-lock"></i>
                    </span>
                    Security Settings
                </h3>

                <?php if ($message): ?>
                    <div class="p-5 rounded-[1.5rem] mb-8 flex items-center space-x-4 <?php echo $messageType === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-red-500/10 border-red-500/30 text-red-400'; ?> border animate-in fade-in slide-in-from-top-4 duration-500">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?php echo $messageType === 'success' ? 'bg-emerald-500/20' : 'bg-red-500/20'; ?>">
                            <i class="fas <?php echo $messageType === 'success' ? 'fa-check' : 'fa-times'; ?>"></i>
                        </div>
                        <div class="text-sm font-bold"><?php echo htmlspecialchars($message); ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div class="group">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3 px-1">Current Password</label>
                            <div class="relative">
                                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                    <i class="fas fa-key text-sm"></i>
                                </span>
                                <input type="password" name="current_password" required class="w-full bg-slate-900/50 border border-slate-800/50 rounded-2xl pl-12 pr-6 py-4 text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium" placeholder="Enter current password">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group">
                                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3 px-1">New Password</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                        <i class="fas fa-shield-alt text-sm"></i>
                                    </span>
                                    <input type="password" name="new_password" required class="w-full bg-slate-900/50 border border-slate-800/50 rounded-2xl pl-12 pr-6 py-4 text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium" placeholder="Min. 6 chars">
                                </div>
                            </div>
                            <div class="group">
                                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3 px-1">Confirm New Password</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-indigo-400 transition-colors">
                                        <i class="fas fa-check-circle text-sm"></i>
                                    </span>
                                    <input type="password" name="confirm_password" required class="w-full bg-slate-900/50 border border-slate-800/50 rounded-2xl pl-12 pr-6 py-4 text-white placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all font-medium" placeholder="Repeat new password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" name="change_password" class="w-full btn-primary text-white font-black py-5 rounded-2xl shadow-2xl shadow-indigo-600/20 active:scale-[0.98] transition-all flex items-center justify-center space-x-3 text-sm uppercase tracking-widest">
                            <span>Update Security Credentials</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <p class="text-center text-[10px] text-slate-500 mt-6 font-medium uppercase tracking-wider">
                            <i class="fas fa-info-circle mr-1"></i> Changing your password will not log you out of your current session.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
