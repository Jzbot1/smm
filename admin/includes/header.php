<?php
// admin/includes/header.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Auth.php';

Auth::checkAdmin();

$db = Database::getInstance();
// Fetch site name
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'site_name'");
$site_name = $stmt->fetchColumn() ?: 'SMM Panel';
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'site_favicon'");
$site_favicon = $stmt->fetchColumn() ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo htmlspecialchars($site_name); ?></title>
    <?php if ($site_favicon): ?>
        <link rel="icon" href="<?php echo htmlspecialchars($site_favicon); ?>" type="image/x-icon">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0b0f1a; color: #f8fafc; }
        .glass { background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-link { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-link:hover { background: rgba(99, 102, 241, 0.1); color: #818cf8; }
        .sidebar-link.active { background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(79, 70, 229, 0.1) 100%); color: #818cf8; border-right: 3px solid #6366f1; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
        
        #mobile-sidebar.open { transform: translateX(0); }
    </style>
</head>
<body class="flex h-screen overflow-hidden bg-[#0b0f1a]">

    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[60] hidden transition-opacity duration-300"></div>

    <!-- Mobile Sidebar -->
    <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 w-72 glass z-[70] md:hidden transform -translate-x-full transition-transform duration-300 ease-in-out flex flex-col shadow-2xl">
        <div class="h-20 flex items-center justify-between px-6 border-b border-slate-700/50">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                    <i class="fas fa-shield-alt text-xs"></i>
                </div>
                <h1 class="text-lg font-black text-white tracking-tight"><?php echo htmlspecialchars($site_name); ?></h1>
            </div>
            <button id="close-sidebar" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="px-6 py-4 border-b border-slate-700/30">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-indigo-400">
                    <i class="fas fa-user-circle text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Administrator</p>
                    <p class="text-[10px] text-slate-500 uppercase tracking-widest font-black">Online Now</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
            <?php 
            $current_page = basename($_SERVER['PHP_SELF']);
            $links = [
                ['index.php', 'fas fa-tachometer-alt', 'Dashboard'],
                ['products.php', 'fas fa-th-list', 'Services'],
                ['sync.php', 'fas fa-sync', 'Sync Services'],
                ['orders.php', 'fas fa-shopping-cart', 'Orders'],
                ['users.php', 'fas fa-users', 'Users'],
                ['settings.php', 'fas fa-cog', 'Settings'],
                ['notifications.php', 'fas fa-bell', 'Notifications'],
            ];
            foreach ($links as $link):
                $active = ($current_page == $link[0]) ? 'active' : '';
            ?>
                <a href="<?php echo BASE_URL; ?>/admin/<?php echo $link[0]; ?>" class="sidebar-link <?php echo $active; ?> flex items-center px-4 py-3 text-slate-300 rounded-xl font-medium">
                    <i class="<?php echo $link[1]; ?> w-8 text-lg"></i> <?php echo $link[2]; ?>
                </a>
            <?php endforeach; ?>
            
            <div class="pt-6 mt-6 border-t border-slate-700/30 space-y-2">
                <p class="px-4 text-[10px] font-black text-slate-600 uppercase tracking-widest mb-2">Shortcuts</p>
                <a href="<?php echo BASE_URL; ?>/index" class="flex items-center px-4 py-3 text-indigo-400 rounded-xl hover:bg-indigo-500/10 transition-colors font-medium">
                    <i class="fas fa-external-link-alt w-8"></i> User Panel
                </a>
                <a href="<?php echo BASE_URL; ?>/logout" class="flex items-center px-4 py-3 text-red-400 rounded-xl hover:bg-red-500/10 transition-colors font-medium">
                    <i class="fas fa-power-off w-8"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Desktop Sidebar -->
    <aside class="w-64 glass border-r border-slate-700/50 flex-shrink-0 hidden md:flex flex-col">
        <div class="h-20 flex items-center px-8 border-b border-slate-700/30">
            <h1 class="text-xl font-black text-indigo-400 tracking-tight uppercase"><?php echo htmlspecialchars($site_name); ?></h1>
        </div>
        <nav class="flex-1 overflow-y-auto py-8 px-4 space-y-1">
            <?php foreach ($links as $link): 
                $active = ($current_page == $link[0]) ? 'active' : '';
            ?>
                <a href="<?php echo BASE_URL; ?>/admin/<?php echo $link[0]; ?>" class="sidebar-link <?php echo $active; ?> flex items-center px-4 py-2.5 text-slate-400 rounded-xl font-medium text-sm">
                    <i class="<?php echo $link[1]; ?> w-7 text-base"></i> <?php echo $link[2]; ?>
                </a>
            <?php endforeach; ?>
            
            <div class="pt-6 mt-6 border-t border-slate-800/50">
                <a href="<?php echo BASE_URL; ?>/index" class="flex items-center px-4 py-2.5 text-indigo-400 rounded-xl hover:bg-indigo-500/10 transition-colors text-sm font-medium">
                    <i class="fas fa-external-link-alt w-7"></i> User Panel
                </a>
                <a href="<?php echo BASE_URL; ?>/logout" class="flex items-center px-4 py-2.5 text-red-400 rounded-xl hover:bg-red-500/10 transition-colors text-sm font-medium mt-1">
                    <i class="fas fa-power-off w-7"></i> Logout
                </a>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        <header class="h-16 md:h-20 glass border-b border-slate-700/50 flex items-center justify-between px-4 md:px-8 z-10 shadow-sm">
            <div class="flex items-center space-x-4">
                <button id="open-sidebar" class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 active:scale-95 transition-transform">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-lg font-bold text-white md:block hidden">Admin Dashboard</h2>
                <h2 class="text-lg font-bold text-white md:hidden">Admin</h2>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex flex-col items-end mr-2 hidden sm:flex">
                    <span class="text-[10px] text-slate-500 uppercase font-black tracking-widest">Administrator</span>
                    <span class="text-sm font-bold text-white">Admin Panel</span>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 shadow-lg shadow-indigo-600/10">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </header>
        <main class="flex-1 overflow-y-auto p-4 md:p-8 relative">
