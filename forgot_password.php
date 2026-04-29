<?php
// forgot_password.php
require_once(__DIR__ . '/config/config.php'); 
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/Mailer.php';

Auth::startSession();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: " . BASE_URL . "/admin/index.php");
    } else {
        header("Location: " . BASE_URL . "/index.php");
    }
    exit;
}

$db = Database::getInstance();
$error = '';
$success = '';

$step = $_SESSION['reset_step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_otp'])) {
        $email = trim($_POST['email'] ?? '');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate OTP
                $otp = sprintf("%06d", mt_rand(1, 999999));
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_expires'] = time() + 900; // 15 minutes
                $_SESSION['reset_step'] = 2;
                $step = 2;
                
                // Send Email
                $subject = "Password Reset OTP - " . ($settings['site_name'] ?? 'SMM Panel');
                $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #4f46e5;'>Password Reset Request</h2>
                    <p>Hello " . htmlspecialchars($user['name']) . ",</p>
                    <p>You have requested to reset your password. Use the following One-Time Password (OTP) to proceed:</p>
                    <div style='background: #f3f4f6; padding: 15px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1f2937; border-radius: 8px; margin: 20px 0;'>
                        {$otp}
                    </div>
                    <p>This OTP is valid for 15 minutes.</p>
                    <p>If you did not request this password reset, please ignore this email or contact support if you have concerns.</p>
                </div>";
                Mailer::send($email, $subject, $body);
                $success = "If the email exists in our system, an OTP has been sent.";
            } else {
                // For security, do not reveal if email exists or not
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_otp'] = bin2hex(random_bytes(16)); // Fake impossible OTP
                $_SESSION['reset_expires'] = time() + 900;
                $_SESSION['reset_step'] = 2;
                $step = 2;
                $success = "If the email exists in our system, an OTP has been sent.";
            }
        } else {
            $error = "Please enter a valid email address.";
        }
    } elseif (isset($_POST['verify_otp'])) {
        $otp = trim($_POST['otp'] ?? '');
        if (isset($_SESSION['reset_otp']) && isset($_SESSION['reset_expires'])) {
            if (time() > $_SESSION['reset_expires']) {
                $error = "OTP has expired. Please request a new one.";
                $_SESSION['reset_step'] = 1;
                $step = 1;
            } elseif ($otp === $_SESSION['reset_otp']) {
                $_SESSION['reset_step'] = 3;
                $step = 3;
                $success = "OTP verified. You can now securely reset your password.";
            } else {
                $error = "Invalid OTP. Please try again.";
            }
        } else {
            $error = "Session expired. Please start over.";
            $_SESSION['reset_step'] = 1;
            $step = 1;
        }
    } elseif (isset($_POST['reset_password'])) {
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if ($step === 3 && isset($_SESSION['reset_email'])) {
            if (strlen($password) < 6) {
                $error = "Password must be at least 6 characters long.";
            } elseif ($password !== $confirm) {
                $error = "Passwords do not match.";
            } else {
                // Update password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed, $_SESSION['reset_email']]);
                
                // Clear session
                unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_step']);
                
                $success = "Password reset successfully. Redirecting to login...";
                echo "<script>setTimeout(function() { window.location.href = '" . BASE_URL . "/login.php'; }, 2000);</script>";
                $step = 4; // Done
            }
        } else {
            $error = "Invalid request.";
            $_SESSION['reset_step'] = 1;
            $step = 1;
        }
    } elseif (isset($_POST['cancel'])) {
        unset($_SESSION['reset_email'], $_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_step']);
        header("Location: login.php");
        exit;
    }
}

// Fetch Site Settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('site_name', 'site_logo', 'site_favicon')");
$settings = [];
foreach ($stmt->fetchAll() as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$site_name = $settings['site_name'] ?? 'SMM Panel';
$site_logo = $settings['site_logo'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo htmlspecialchars($site_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }

        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Background Decor -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl"></div>

    <div class="glass p-8 rounded-3xl w-full max-w-md shadow-2xl relative z-10 border border-white/10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-500/20 rounded-2xl flex items-center justify-center text-indigo-400 mx-auto mb-6 shadow-inner">
                <?php if ($step === 1): ?>
                    <i class="fas fa-envelope-open-text text-2xl"></i>
                <?php elseif ($step === 2): ?>
                    <i class="fas fa-shield-alt text-2xl"></i>
                <?php elseif ($step === 3): ?>
                    <i class="fas fa-key text-2xl"></i>
                <?php else: ?>
                    <i class="fas fa-check-circle text-2xl text-green-400"></i>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">
                <?php 
                if ($step === 1) echo "Forgot Password";
                elseif ($step === 2) echo "Enter OTP";
                elseif ($step === 3) echo "Reset Password";
                else echo "Success!";
                ?>
            </h1>
            <p class="text-slate-400 text-sm">
                <?php 
                if ($step === 1) echo "Enter your email address to receive a verification code.";
                elseif ($step === 2) echo "We've sent a 6-digit code to your email.";
                elseif ($step === 3) echo "Enter your new password below.";
                else echo "Your password has been reset successfully.";
                ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-4 rounded-xl mb-6 text-sm flex items-start">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 p-4 rounded-xl mb-6 text-sm flex items-start">
                <i class="fas fa-check-circle mt-0.5 mr-3"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Email Address</label>
                    <input type="email" name="email" required
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all" placeholder="Enter your registered email">
                </div>
                <button type="submit" name="request_otp"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-indigo-600/30 active:scale-95">
                    Send OTP
                </button>
            </form>

        <?php elseif ($step === 2): ?>
            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">6-Digit OTP</label>
                    <input type="text" name="otp" required maxlength="6" pattern="\d{6}"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-center text-2xl tracking-[0.5em] text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all font-mono" placeholder="------">
                </div>
                <button type="submit" name="verify_otp"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-indigo-600/30 active:scale-95 mb-4">
                    Verify Code
                </button>
                <button type="submit" name="cancel" formnovalidate
                    class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-4 rounded-2xl transition-all">
                    Cancel
                </button>
            </form>

        <?php elseif ($step === 3): ?>
            <form method="POST" action="">
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">New Password</label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all" placeholder="Min. 6 characters">
                </div>
                <div class="mb-8">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 px-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6"
                        class="w-full bg-slate-900/50 border border-slate-700/50 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all" placeholder="Repeat new password">
                </div>
                <button type="submit" name="reset_password"
                    class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-green-500/30 active:scale-95">
                    Save New Password
                </button>
            </form>

        <?php elseif ($step === 4): ?>
            <a href="<?php echo BASE_URL; ?>/login.php" class="w-full flex justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-indigo-600/30">
                Back to Login
            </a>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <p class="mt-8 text-center text-sm text-slate-400 font-medium">
                Remember your password? <a href="<?php echo BASE_URL; ?>/login.php" class="text-indigo-400 hover:text-indigo-300 transition-colors">Sign in</a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
