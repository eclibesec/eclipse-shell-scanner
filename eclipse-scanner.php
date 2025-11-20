<?php
// =======================================================================
//  ECLIPSE SECURITY LABS - SHELL SCANNER
// =======================================================================
date_default_timezone_set('Asia/Jakarta');
$forbiden_function = [
    "eval","system","create_function","assert","chdir",
    "base64_decode","shell_exec","exec","passthru","popen",
    "_halt_compiler","file_get_contents(","shell(","base64_encode(",
    "webconsole","uploader","hacked","move_uploaded_file",
    "hex2bin(","bin2hex(","WSOstripslashes","AGUSTUS_17_1945",
    "Cyto","con7ext","Fileman","68746d6c7370656369616c6368617273",
    "xiaoxiannv","ruzhu","edoced_46esab","Solevisible","Zeerx7",
    "phpFileManager","dZNOmgVpUDdbg","indoxploit","mini shell",
    "minishell","tinyfilemanager.github.io","xleet","b374k",
    "set_magic_quotes_runtime(","pastebin","alfa","filemanager",
];
$malicious_folders = [
    "ALFA_DATA/alfacgiapi"
];

$file_scanned = 0;
$total_files = isset($_GET['act']) ? 0 : countFiles(".");
if (isset($_GET['act'])) {
    if ($_GET['act'] == "scan") {
        echoIframeCSS();
        echo "<script>var infected_list=[];</script>";
        $dir = isset($_GET['dir']) ? $_GET['dir'] : ".";
        if(!is_dir($dir)) $dir = ".";
        scan_file($dir);
        echo "<script>window.parent.scanFinished = true;</script>";
        exit;
    }
    if ($_GET['act'] == "filelist") {
        echoIframeCSS();
        if (!isset($_GET['done']) || $_GET['done'] != 1) {
            echoErrorState("SCAN INCOMPLETE", "Please finish the scan before viewing the file list.");
            exit;
        }
        $dir = isset($_GET['dir']) ? $_GET['dir'] : ".";
        if(!is_dir($dir)) $dir = ".";
        list_files($dir);
        exit;
    }
    if ($_GET['act'] == "view" && isset($_GET['file'])) {
        echoIframeCSS();
        $file = $_GET['file'];
        if(file_exists($file)){
            echoViewFile($file);
        } else {
            echoErrorState("FILE NOT FOUND", "The requested file could not be found.");
        }
        exit;
    }
    if ($_GET['act'] == "edit" && isset($_GET['file'])) {
        echoIframeCSS();
        $file = $_GET['file'];
        if(file_exists($file)){
            echoEditFile($file);
        } else {
            echoErrorState("FILE NOT FOUND", "The requested file could not be found.");
        }
        exit;
    }
    if ($_GET['act'] == "save" && isset($_POST['file']) && isset($_POST['content'])) {
        $file = $_POST['file'];
        $content = $_POST['content'];
        if(file_exists($file)){
            file_put_contents($file, $content);
            echo "<script>
                parent.showModal('Success', 'File saved successfully!', 'success', function(){
                    window.location='?act=edit&file=".urlencode($file)."';
                });
            </script>";
        } else {
            echo "<script>
                parent.showModal('Error', 'File not found!', 'error', function(){
                    window.history.back();
                });
            </script>";
        }
        exit;
    }
    if ($_GET['act'] == "delete" && isset($_GET['file'])) {
        $file = $_GET['file'];
        if(file_exists($file)){
            unlink($file);
            
            if(isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'success', 'message' => 'File deleted successfully']);
                exit;
            }

            $safe_file = json_encode($file);
            echo "<script>
                if(parent.infected_files){
                    const index = parent.infected_files.indexOf($safe_file);
                    if (index > -1) {
                        parent.infected_files.splice(index, 1);
                    }
                }
                parent.showModal('Deleted', 'File deleted successfully!', 'success', function(){
                    parent.showInfectedOnly();
                });
            </script>";
        } else {
            if(isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'File not found']);
                exit;
            }

            echo "<script>
                parent.showModal('Error', 'File not found!', 'error', function(){
                    parent.showInfectedOnly();
                });
            </script>";
        }
        exit;
    }
    if ($_GET['act'] == "get_nav_data") {
        $dir = isset($_GET['dir']) ? $_GET['dir'] : '.';
        if(!is_dir($dir)) $dir = ".";
        
        $real_dir = realpath($dir);
        $subdirs = [];
        if ($scandir = @scandir($dir)) {
            foreach($scandir as $item){
                if($item == '.' || $item == '..') continue;
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if(is_dir($path)) {
                    $subdirs[] = $item;
                }
            }
        }
        
        $response = [
            'current' => $real_dir,
            'display' => $dir,
            'parent' => dirname($real_dir),
            'subdirs' => $subdirs,
            'total_files' => countFiles($dir)
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    if ($_GET['act'] == "ready") {
        echoReadyBase();
        exit;
    }

} else {
    echoMainUI();
    exit;
}
function echoMainUI(){
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eclipse Security Labs - Shell Scanner </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                            950: '#020617',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-200 font-sans min-h-screen p-4 md:p-8 selection:bg-violet-500/30">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b border-slate-800 pb-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-violet-500/10 rounded-xl border border-violet-500/20 shadow-[0_0_15px_rgba(139,92,246,0.15)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-violet-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Eclipse Security Labs</h1>
                    <p class="text-slate-400 text-sm font-medium">SHELL - SCANNER</p>
                </div>
            </div>
            <div class="flex items-center gap-2 text-xs font-mono bg-slate-900 px-4 py-2 rounded-full border border-slate-800 text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                SYSTEM READY
            </div>
        </div>
        <div class="glass-panel p-4 rounded-xl border border-slate-800 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex items-center gap-3 w-full md:w-auto overflow-hidden flex-1">
                <div class="p-2 bg-blue-500/10 rounded-lg border border-blue-500/20 shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </div>
                <div class="flex flex-col min-w-0 flex-1 w-full">
                    <span class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Current Directory</span>
                    <form onsubmit="event.preventDefault(); navigate(document.getElementById('currentPathInput').value);" class="flex gap-2 items-center w-full">
                        <input id="currentPathInput" type="text" class="w-full bg-slate-900/50 border border-slate-700 rounded px-3 py-1.5 text-sm font-mono text-blue-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all placeholder-slate-600" placeholder="/path/to/scan" value="Loading...">
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded font-medium transition-colors shadow-lg shadow-blue-900/20 shrink-0">Go</button>
                    </form>
                </div>
            </div>
            
            <div class="flex items-center gap-2 w-full md:w-auto shrink-0">
                <button onclick="navigateUp()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg text-sm font-medium transition-colors border border-slate-700 flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    Up Level
                </button>
                
                <div class="relative w-full md:w-64">
                    <select id="subDirSelect" onchange="navigate(this.value)" class="w-full appearance-none bg-slate-900 border border-slate-700 text-slate-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 pr-8">
                        <option value="">Go to subdirectory...</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glass-panel p-5 rounded-xl border-l-4 border-l-blue-500 hover:bg-slate-800/50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Files Scanned</span>
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div class="text-2xl font-bold text-white" id="f_scan">0</div>
            </div>
            <div class="glass-panel p-5 rounded-xl border-l-4 border-l-red-500 hover:bg-slate-800/50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Infected</span>
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div class="text-2xl font-bold text-red-400" id="f_inf">0</div>
            </div>
            <div class="glass-panel p-5 rounded-xl border-l-4 border-l-emerald-500 hover:bg-slate-800/50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Speed</span>
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="text-2xl font-bold text-white" id="f_speed">0 f/s</div>
            </div>
            <div class="glass-panel p-5 rounded-xl border-l-4 border-l-slate-500 hover:bg-slate-800/50 transition-colors">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">Total Files</span>
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <div class="text-2xl font-bold text-white" id="totalFilesDisplay"><?php echo $GLOBALS['total_files']; ?></div>
            </div>
        </div>
        <div class="glass-panel rounded-2xl p-6 space-y-6">
            <div class="flex flex-wrap gap-3">
                <button onclick="startScan()" class="flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-500 active:bg-violet-700 text-white rounded-lg font-medium transition-all shadow-lg shadow-violet-900/20 hover:shadow-violet-900/40 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Start Scan
                </button>

                <button id="btnFileList" onclick="showFileList()" disabled class="flex items-center gap-2 px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition-all border border-slate-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-slate-800 disabled:transform-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Show File List
                </button>

                <button onclick="showInfectedOnly()" class="flex items-center gap-2 px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition-all border border-slate-700">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Show Infected Only
                </button>

                <button onclick="resetFrame()" class="flex items-center gap-2 px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg font-medium transition-all border border-slate-700 ml-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Reset
                </button>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-sm text-slate-400 font-medium">
                    <span>System Scan Progress</span>
                    <span id="progressText">0%</span>
                </div>
                <div class="h-3 bg-slate-950 rounded-full overflow-hidden border border-slate-800/50 shadow-inner">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-violet-600 to-indigo-500 w-0 transition-all duration-300 ease-out relative shadow-[0_0_10px_rgba(139,92,246,0.5)]">
                        <div class="absolute inset-0 bg-white/20 animate-[pulse_2s_infinite]"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#0a0e17] rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between px-4 py-2 bg-slate-900 border-b border-slate-800">
                <div class="flex gap-1.5">
                    <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500/20 border border-yellow-500/50"></div>
                    <div class="w-3 h-3 rounded-full bg-emerald-500/20 border border-emerald-500/50"></div>
                </div>
                <div class="text-xs text-slate-500 font-mono">scanner_output.log</div>
            </div>
            <iframe id="scanframe" src="?act=ready" class="w-full h-[500px] border-none bg-[#0a0e17]"></iframe>
        </div>

        <div class="text-center text-slate-600 text-xs py-4">
            &copy; <?php echo date("Y"); ?> Eclipse Security Labs. All rights reserved.
        </div>
        <div id="customModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
            <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 max-w-md w-full shadow-2xl transform scale-95 transition-transform duration-300" id="modalContent">
                <h3 id="modalTitle" class="text-xl font-bold mb-2 text-white">Title</h3>
                <p id="modalMessage" class="text-slate-300 mb-6">Message goes here...</p>
                <div class="flex justify-end gap-3" id="modalActions">
                </div>
            </div>
        </div>

    </div>

<script>
let total=0;
let total_files = <?php echo $GLOBALS['total_files']; ?>;
let currentDir = ".";
let parentDir = "";

window.scanFinished = false;
window.infected_files = [];

document.addEventListener('DOMContentLoaded', function() {
    navigate('.');
});

function showModal(title, message, type = 'info', callback = null) {
    const modal = document.getElementById('customModal');
    const content = document.getElementById('modalContent');
    const titleEl = document.getElementById('modalTitle');
    const msgEl = document.getElementById('modalMessage');
    const actions = document.getElementById('modalActions');

    titleEl.innerText = title;
    msgEl.innerText = message;
    titleEl.className = 'text-xl font-bold mb-2';
    if(type === 'error') titleEl.classList.add('text-red-500');
    else if(type === 'success') titleEl.classList.add('text-emerald-500');
    else if(type === 'warning') titleEl.classList.add('text-yellow-500');
    else titleEl.classList.add('text-blue-500');
    actions.innerHTML = '';
    
    if (type === 'confirm') {
        const btnCancel = document.createElement('button');
        btnCancel.className = 'px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors font-medium';
        btnCancel.innerText = 'Cancel';
        btnCancel.onclick = closeModal;
        actions.appendChild(btnCancel);

        const btnOk = document.createElement('button');
        btnOk.className = 'px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white transition-colors font-medium shadow-lg shadow-red-900/20';
        btnOk.innerText = 'Delete';
        btnOk.onclick = () => {
            closeModal();
            if(callback) callback();
        };
        actions.appendChild(btnOk);
    } else {
        const btnOk = document.createElement('button');
        btnOk.className = 'px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white transition-colors font-medium shadow-lg shadow-violet-900/20';
        btnOk.innerText = 'OK';
        btnOk.onclick = () => {
            closeModal();
            if(callback) callback();
        };
        actions.appendChild(btnOk);
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
        content.classList.add('scale-100');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('customModal');
    const content = document.getElementById('modalContent');
    
    modal.classList.add('opacity-0');
    content.classList.remove('scale-100');
    content.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function navigate(dir) {
    if(!dir) return;
    let input = document.getElementById('currentPathInput');
    input.value = "Loading...";
    input.disabled = true;
    document.getElementById('subDirSelect').disabled = true; 
    fetch('?act=get_nav_data&dir=' + encodeURIComponent(dir))
        .then(response => response.json())
        .then(data => {
            currentDir = data.display;
            parentDir = data.parent;
            total_files = data.total_files;
            input.value = data.current;
            input.title = data.current;
            input.disabled = false;
            document.getElementById('totalFilesDisplay').innerText = data.total_files;
            let select = document.getElementById('subDirSelect');
            select.innerHTML = '<option value="">Go to subdirectory...</option>';
            data.subdirs.forEach(sub => {
                let option = document.createElement('option');
                let nextPath = (currentDir === '.' ? '' : currentDir + '/') + sub;
                nextPath = nextPath.replace('//', '/');
                
                option.value = nextPath;
                option.text = "📂 " + sub;
                select.appendChild(option);
            });
            select.disabled = false;
            resetFrame();
        })
        .catch(err => {
            console.error('Navigation error:', err);
            showModal('Navigation Error', 'Failed to navigate to directory.', 'error');
            let input = document.getElementById('currentPathInput');
            input.value = "Error";
            input.disabled = false;
        });
}

function navigateUp() {
    if(parentDir) {
        navigate(parentDir);
    } else {
        navigate('..');
    }
}

function startScan(){
    window.infected_files = [];
    document.getElementById('scanframe').src='?act=scan&dir=' + encodeURIComponent(currentDir);
}

function deleteFile(file) {
    showModal("Confirm Deletion", "Are you sure you want to delete this file permanently? This action cannot be undone.", "confirm", function(){
        fetch('?act=delete&ajax=1&file=' + encodeURIComponent(file))
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    window.infected_files = window.infected_files.filter(f => f !== file);
                    
                    showModal('Deleted', 'File deleted successfully!', 'success', function(){
                        showInfectedOnly();
                    });
                } else {
                    showModal('Error', data.message || 'Failed to delete file', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showModal('Error', 'Network error occurred', 'error');
            });
    });
}

function resetFrame(){
    window.scanFinished = false;
    window.infected_files = [];
    document.getElementById("btnFileList").disabled = true;
    document.getElementById("btnFileList").classList.add("opacity-50", "cursor-not-allowed");
    document.getElementById('scanframe').src='?act=ready';
    document.getElementById("progressBar").style.width="0%";
    document.getElementById("progressText").innerText="0%";
    document.getElementById("f_scan").innerText="0";
    document.getElementById("f_inf").innerText="0";
    document.getElementById("f_speed").innerText="0 f/s";
}

function showFileList(){
    if (!window.scanFinished){
        showModal('Scan Incomplete', 'Please wait for the scan to finish before viewing files.', 'warning');
        return;
    }
    document.getElementById('scanframe').src='?act=filelist&done=1&dir=' + encodeURIComponent(currentDir);
}

function showInfectedOnly(){
    let frm=document.getElementById('scanframe').contentWindow;
    if (!window.infected_files && !window.scanFinished){
        showModal('No Data', 'No scan results available yet.', 'info');
        return;
    }
    let html="<div class='p-4 space-y-2'>";
    html+="<h2 class='text-xl font-bold text-red-400 mb-4 border-b border-red-900/50 pb-2'>⚠️ INFECTED FILES DETECTED</h2>";
    
    if(window.infected_files.length === 0) {
        html+="<div class='text-emerald-400 p-4 bg-emerald-500/10 rounded border border-emerald-500/20'>No infected files found. System is clean.</div>";
    } else {
        window.infected_files.forEach(f=>{
            let enc = encodeURIComponent(f);     
            html+="<div class='flex items-center justify-between gap-3 p-3 bg-slate-900/50 hover:bg-red-500/5 rounded-lg transition-colors border border-slate-800 hover:border-red-500/30 group'>";
            html+="<div class='flex items-center gap-3 overflow-hidden'>";
            html+="<span class='bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded shrink-0'>INFECTED</span>";
            html+="<span class='text-red-200 font-mono text-sm truncate'>"+f+"</span>";
            html+="</div>";
            html+="<div class='flex items-center gap-2'>";
            html+="<a href='?act=view&file="+enc+"' class='p-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-md transition-colors' title='View File'>";
            html+="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg>";
            html+="</a>";
            html+="<a href='?act=edit&file="+enc+"' class='p-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md transition-colors' title='Edit File'>";
            html+="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'></path></svg>";
            html+="</a>";
            html+="<a href='javascript:void(0)' onclick='parent.deleteFile(\""+f.replace(/\\/g, '\\\\').replace(/'/g, "\\'")+"\")' class='p-2 bg-red-600 hover:bg-red-500 text-white rounded-md transition-colors' title='Delete File'>";
            html+="<svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'></path></svg>";
            html+="</a>";

            html+="</div></div>";
        });
    }
    html+="</div>";
    
    let frmDoc = frm.document;
    frmDoc.body.innerHTML=html;
    frmDoc.body.className = "bg-[#0a0e17] text-slate-300 p-4";
    let link = frmDoc.createElement("link");
    link.rel = "stylesheet";
    link.href = "https://cdn.tailwindcss.com"; 
    frmDoc.head.appendChild(link);
}

setInterval(function(){
    let frm = document.getElementById('scanframe').contentWindow;
    if (!frm.document) return;
    let scanned = frm.document.querySelectorAll('.scan-item').length;
    let infected = frm.document.querySelectorAll('.infected-tag').length;

    document.getElementById("f_scan").innerHTML = scanned;
    document.getElementById("f_inf").innerHTML = infected;
    document.getElementById("f_speed").innerHTML = (scanned-total)+" f/s";

    let percent = 0;
    if(total_files > 0) {
        percent = (scanned / total_files * 100);
    }
    if (percent > 100) percent = 100;
    
    document.getElementById("progressBar").style.width = percent + "%";
    document.getElementById("progressText").innerText = Math.round(percent) + "%";

    if (window.scanFinished===true){
        let btn = document.getElementById("btnFileList");
        btn.disabled = false;
        btn.classList.remove("opacity-50", "cursor-not-allowed");
    }

    total = scanned;

},800);
</script>

</body>
</html>
<?php
}
function echoIframeCSS(){
echo '<script src="https://cdn.tailwindcss.com"></script>';
echo "<style>
    body { background:#0a0e17; color:#cbd5e1; font-family: 'Courier New', monospace; padding:20px; }
    .scan-item { border-bottom: 1px solid #1e293b; padding: 4px 0; display:block; }
    .path { color: #94a3b8; }
    .infected-tag {
        background:#ef4444;
        color:white;
        padding:2px 6px; 
        border-radius:4px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 10px;
    }
    .safe-tag {
        background:#10b981;
        color:white;
        padding:2px 6px; 
        border-radius:4px;
        font-size: 12px;
        font-weight: bold;
        margin-left: 10px;
    }
    .ext-header {
        margin-top: 30px;
        margin-bottom: 10px;
        color: #a78bfa;
        font-weight: bold;
        font-size: 18px;
        border-bottom: 1px solid #4c1d95;
        padding-bottom: 5px;
        font-family: sans-serif;
    }
</style>";
}
function echoReadyBase(){
echo '<script src="https://cdn.tailwindcss.com"></script>';
echo "<div class='flex flex-col items-center justify-center h-full text-center space-y-6 pt-20'>
        <div class='p-6 bg-slate-800/50 rounded-full mb-4'>
            <svg class='w-16 h-16 text-slate-600' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M21 21l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'></path></svg>
        </div>
        <h2 class='text-2xl font-bold text-slate-300'>Ready to Scan</h2>
        <p class='text-slate-500 max-w-md'>Click 'Start Scan' to begin analyzing files for malicious code patterns and backdoors.</p>
        <a href='?act=scan' class='inline-flex items-center gap-2 px-8 py-3 bg-violet-600 hover:bg-violet-500 text-white rounded-lg font-medium transition-all shadow-lg shadow-violet-900/20'>
            Start Scan Now
        </a>
      </div>";
}
function echoErrorState($title, $msg){
    echo "<div class='flex flex-col items-center justify-center h-full text-center p-10'>";
    echo "<div class='text-red-500 text-5xl mb-4'>⚠️</div>";
    echo "<h2 class='text-xl font-bold text-red-400 mb-2'>$title</h2>";
    echo "<p class='text-slate-400'>$msg</p>";
    echo "</div>";
}
function echoViewFile($file){
    $content = htmlspecialchars(file_get_contents($file));
    echo "<div class='flex flex-col h-full'>";
    echo "<div class='bg-slate-900 p-3 border-b border-slate-800 flex justify-between items-center sticky top-0 z-10'>";
    echo "<div class='flex items-center gap-2 text-sm text-slate-300'>";
    echo "<svg class='w-4 h-4 text-emerald-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 12a3 3 0 11-6 0 3 3 0 016 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'></path></svg>";
    echo "Viewing: <span class='font-mono text-emerald-300'>$file</span>";
    echo "</div>";
    echo "<button onclick='parent.showInfectedOnly()' class='px-3 py-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 rounded transition-colors'>Back to Infected List</button>";
    echo "</div>";
    echo "<pre class='flex-1 bg-[#0a0e17] text-slate-300 font-mono text-sm p-4 overflow-auto'><code>$content</code></pre>";
    echo "</div>";
}
function echoEditFile($file){
    $content = htmlspecialchars(file_get_contents($file));
    echo "<form action='?act=save' method='POST' class='flex flex-col h-full'>";
    echo "<input type='hidden' name='file' value='$file'>";
    echo "<div class='bg-slate-900 p-3 border-b border-slate-800 flex justify-between items-center sticky top-0 z-10'>";
    echo "<div class='flex items-center gap-2 text-sm text-slate-300'>";
    echo "<svg class='w-4 h-4 text-blue-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'></path></svg>";
    echo "Editing: <span class='font-mono text-blue-300'>$file</span>";
    echo "</div>";
    echo "<div class='flex gap-2'>";
    echo "<button type='button' onclick='parent.showInfectedOnly()' class='px-3 py-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 rounded transition-colors'>Cancel</button>";
    echo "<button type='submit' class='px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-500 text-white rounded transition-colors flex items-center gap-1'>Save Changes</button>";
    echo "</div>";
    echo "</div>";
    echo "<textarea name='content' class='flex-1 bg-[#0a0e17] text-slate-300 font-mono text-sm p-4 resize-none focus:outline-none' spellcheck='false'>$content</textarea>";
    echo "</form>";
}
function scan_file($path){
    global $forbiden_function,$file_scanned,$malicious_folders;

    $files=array_diff(scandir($path),['.','..']);

    foreach($files as $file){

        $full="$path/$file";
        foreach($malicious_folders as $bad){
            if (stripos($full,$bad)!==false){
                echo "<div class='scan-item'><span class='path'>$full</span> <span class='infected-tag'>INFECTED (ALFA BACKDOOR)</span></div>";
                $safe_full = json_encode($full);
                echo "<script>infected_list.push($safe_full); try{parent.infected_files.push($safe_full);}catch(e){}</script>";
                continue 2;
            }
        }
        if (is_dir($full)){
            scan_file($full);
        } 
        else {

            $isinf = filecheck($full);
            $file_scanned++;

            if ($isinf) {
                $safe_full = json_encode($full);
                echo "<script>infected_list.push($safe_full); try{parent.infected_files.push($safe_full);}catch(e){}</script>";
            }

            echo "<div class='scan-item'><span class='path'>$full</span> ".(
                $isinf ? "<span class='infected-tag'>INFECTED</span>" :
                         "<span class='safe-tag'>SAFE</span>"
            )."</div>";
            if($file_scanned % 5 == 0) {
                @ob_flush();
                @flush();
            }
        }
    }
}
function filecheck($path){
    global $forbiden_function;

    if (!file_exists($path)) return false;

    $ext=strtolower(pathinfo($path,PATHINFO_EXTENSION)); 
    if (!in_array($ext,['php','phtml'])) return false;

    $data=@file_get_contents($path);
    if (!$data) return false;

    $pattern="/(".implode("|",array_map('preg_quote',$forbiden_function)).")/i";

    if (preg_match($pattern,$data)) return true;

    return false;
}
function list_files($dir){

    echo "<div class='p-4'>";
    echo "<h2 class='text-2xl font-bold text-white mb-6'>FILE LIST EXPLORER</h2>";

    $group=[];

    $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach($it as $file){
        if ($file->isDir()) continue;

        $path=$file->getPathname();
        $ext=strtolower(pathinfo($path,PATHINFO_EXTENSION));
        if ($ext=="") $ext="NOEXT";

        $group[$ext][]=$path;
    }
    ksort($group);
    if (isset($group['php'])) {
        $php = $group['php'];
        unset($group['php']);
        $group = array_merge(['php'=>$php], $group);
    }

    foreach($group as $ext=>$items){
        echo "<div class='ext-header'>TYPE: ".strtoupper($ext)." <span class='text-sm text-slate-500 font-normal ml-2'>(".count($items)." files)</span></div>";
        echo "<div class='space-y-1 mb-6'>";
        foreach($items as $p){
            $ts=date("Y-m-d H:i:s",filemtime($p));
            echo "<div class='flex gap-4 text-sm hover:bg-slate-800/50 p-1 rounded'>";
            echo "<span class='text-slate-500 font-mono w-40 shrink-0'>$ts</span>";
            echo "<span class='text-slate-300 font-mono break-all'>$p</span>";
            echo "</div>";
        }
        echo "</div>";
    }
    echo "</div>";
}
function countFiles($dir){
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $c=0;
    foreach($it as $f){
        if ($f->isFile()) $c++;
    }
    return $c;
}

?>
