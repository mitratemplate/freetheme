<?php
/**
 * AI Assistant Page - Powered by GapGPT
 */
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (!isLoggedIn()) {
    redirect('login');
}

$user = getCurrentUser();
$messages = [];

// Handle AJAX request for AI chat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'chat') {
    header('Content-Type: application/json');
    
    $userMessage = sanitize($_POST['message'] ?? '');
    $context = $_POST['context'] ?? 'general';
    
    if (empty($userMessage)) {
        echo json_encode(['success' => false, 'message' => 'پیام خالی است']);
        exit;
    }
    
    // Build context-aware prompt
    $systemPrompts = [
        'hr_analysis' => 'شما دستیار هوشمند منابع انسانی هستید. تحلیل‌های عمیق درباره کارکنان، ترک خدمت، عملکرد و پیشنهادات بهبود ارائه دهید.',
        'payroll' => 'شما متخصص حقوق و دستمزد مطابق قوانین کار ایران ۱۴۰۵ هستید. محاسبات دقیق و راهنمایی درباره بیمه، مالیات و مزایا ارائه دهید.',
        'recruitment' => 'شما متخصص استخدام و جذب نیرو هستید. تحلیل رزومه، سوالات مصاحبه و ارزیابی کاندیداها را انجام دهید.',
        'performance' => 'شما مشاور ارزیابی عملکرد هستید. تحلیل KPI، بازخورد سازنده و برنامه‌های توسعه فردی پیشنهاد دهید.',
        'legal' => 'شما مشاور حقوقی کار هستید. راهنمایی درباره قوانین کار، قراردادها، مرخصی‌ها و حل اختلافات کاری ارائه دهید.',
        'general' => 'شما دستیار هوشمند اتوماسیون اداری هستید. به سوالات کاربران درباره سیستم، فرآیندها و بهترین روش‌ها پاسخ دهید.'
    ];
    
    $prompt = $systemPrompts[$context] . "\n\nسوال کاربر: " . $userMessage;
    
    // Call GapGPT API
    $response = aiRequest($prompt, ['context' => $context]);
    
    // Save conversation to database
    $db = Database::getInstance();
    $db->insert('ai_conversations', [
        'user_id' => $user['id'],
        'context' => $context,
        'user_message' => $userMessage,
        'ai_response' => $response,
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Get recent conversations
$db = Database::getInstance();
$recentConversations = $db->fetchAll("
    SELECT * FROM ai_conversations 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
", [$user['id']]);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دستیار هوشمند | اتوماسیون اداری</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; }
        .typing-indicator span {
            animation: blink 1.4s infinite both;
        }
        .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
        .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0% { opacity: 0.2; }
            20% { opacity: 1; }
            100% { opacity: 0.2; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index" class="text-blue-600 hover:text-blue-700">← بازگشت به داشبورد</a>
                <h1 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 ml-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    دستیار هوشمند AI
                </h1>
                <div></div>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8">
        <!-- Context Selection -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">حوزه تخصصی را انتخاب کنید:</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <button onclick="setContext('general')" class="context-btn bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg p-3 text-center transition active-context" data-context="general">
                    <span class="block text-2xl mb-1">💬</span>
                    <span class="text-sm font-medium">عمومی</span>
                </button>
                <button onclick="setContext('hr_analysis')" class="context-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-3 text-center transition" data-context="hr_analysis">
                    <span class="block text-2xl mb-1">👥</span>
                    <span class="text-sm font-medium">تحلیل HR</span>
                </button>
                <button onclick="setContext('payroll')" class="context-btn bg-yellow-50 hover:bg-yellow-100 border-2 border-yellow-200 rounded-lg p-3 text-center transition" data-context="payroll">
                    <span class="block text-2xl mb-1">💰</span>
                    <span class="text-sm font-medium">حقوق و دستمزد</span>
                </button>
                <button onclick="setContext('recruitment')" class="context-btn bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-3 text-center transition" data-context="recruitment">
                    <span class="block text-2xl mb-1">📋</span>
                    <span class="text-sm font-medium">استخدام</span>
                </button>
                <button onclick="setContext('performance')" class="context-btn bg-red-50 hover:bg-red-100 border-2 border-red-200 rounded-lg p-3 text-center transition" data-context="performance">
                    <span class="block text-2xl mb-1">📊</span>
                    <span class="text-sm font-medium">ارزیابی عملکرد</span>
                </button>
                <button onclick="setContext('legal')" class="context-btn bg-indigo-50 hover:bg-indigo-100 border-2 border-indigo-200 rounded-lg p-3 text-center transition" data-context="legal">
                    <span class="block text-2xl mb-1">⚖️</span>
                    <span class="text-sm font-medium">مشاوره حقوقی</span>
                </button>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div id="chat-messages" class="h-96 overflow-y-auto p-6 space-y-4">
                <!-- Welcome Message -->
                <div class="flex items-start">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center ml-3 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div class="bg-purple-50 rounded-2xl rounded-tr-none p-4 max-w-2xl">
                        <p class="text-gray-800">سلام! من دستیار هوشمند شما هستم. چطور می‌توانم کمکتان کنم؟</p>
                        <p class="text-xs text-gray-500 mt-2">می‌توانید درباره موارد زیر سوال بپرسید:</p>
                        <ul class="text-sm text-gray-600 mt-1 space-y-1 mr-4 list-disc">
                            <li>تحلیل آمار کارکنان و ترک خدمت</li>
                            <li>محاسبات حقوق، بیمه و مالیات</li>
                            <li>ارزیابی رزومه و سوالات مصاحبه</li>
                            <li>تحلیل عملکرد و KPI</li>
                            <li>مشاوره حقوق کار و قراردادها</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="border-t p-4">
                <form id="chat-form" class="flex gap-3">
                    <input type="text" id="user-input" 
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                           placeholder="سوال خود را بنویسید..." 
                           autocomplete="off">
                    <button type="submit" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center">
                        <span>ارسال</span>
                        <svg class="w-5 h-5 mr-2 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Conversations -->
        <?php if (!empty($recentConversations)): ?>
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">مکالمات اخیر</h2>
            <div class="space-y-3">
                <?php foreach ($recentConversations as $conv): ?>
                    <div class="border rounded-lg p-4 hover:bg-gray-50 transition cursor-pointer" onclick="loadConversation(<?= $conv['id'] ?>)">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars(mb_substr($conv['user_message'], 0, 80)) ?>...</p>
                                <p class="text-sm text-gray-500 mt-1"><?= jalaliDate($conv['created_at']) ?></p>
                            </div>
                            <span class="text-xs bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($conv['context']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        let currentContext = 'general';
        
        function setContext(context) {
            currentContext = context;
            document.querySelectorAll('.context-btn').forEach(btn => {
                btn.classList.remove('border-purple-400', 'bg-purple-100');
                btn.classList.add('border-' + btn.dataset.context.split('_')[0] + '-200');
            });
            const activeBtn = document.querySelector('[data-context="' + context + '"]');
            activeBtn.classList.remove('border-' + context.split('_')[0] + '-200');
            activeBtn.classList.add('border-purple-400', 'bg-purple-100');
        }
        
        // Set initial active state
        setContext('general');
        
        document.getElementById('chat-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const input = document.getElementById('user-input');
            const message = input.value.trim();
            if (!message) return;
            
            // Add user message to chat
            addMessage(message, 'user');
            input.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            try {
                const response = await fetch('ai-assistant', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: new URLSearchParams({action: 'chat', message: message, context: currentContext})
                });
                
                const data = await response.json();
                hideTypingIndicator();
                
                if (data.success) {
                    addMessage(data.response, 'ai');
                } else {
                    addMessage('خطا: ' + data.message, 'ai');
                }
            } catch (error) {
                hideTypingIndicator();
                addMessage('خطا در ارتباط با سرور', 'ai');
            }
        });
        
        function addMessage(text, sender) {
            const container = document.getElementById('chat-messages');
            const isUser = sender === 'user';
            
            const div = document.createElement('div');
            div.className = 'flex items-start ' + (isUser ? 'flex-row-reverse' : '');
            div.innerHTML = `
                <div class="w-10 h-10 ${isUser ? 'bg-blue-100' : 'bg-purple-100'} rounded-full flex items-center justify-center ${isUser ? 'mr-3' : 'ml-3'} flex-shrink-0">
                    ${isUser ? '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>' : '<svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>'}
                </div>
                <div class="${isUser ? 'bg-blue-50 rounded-tl-none' : 'bg-purple-50 rounded-tr-none'} rounded-2xl p-4 max-w-2xl">
                    <p class="text-gray-800 whitespace-pre-wrap">${escapeHtml(text)}</p>
                    <p class="text-xs text-gray-500 mt-2">${new Date().toLocaleTimeString('fa-IR')}</p>
                </div>
            `;
            
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }
        
        function showTypingIndicator() {
            const container = document.getElementById('chat-messages');
            const div = document.createElement('div');
            div.id = 'typing-indicator';
            div.className = 'flex items-start';
            div.innerHTML = `
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center ml-3 flex-shrink-0">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="bg-purple-50 rounded-2xl rounded-tr-none p-4">
                    <div class="typing-indicator flex space-x-1 space-x-reverse">
                        <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                        <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                        <span class="w-2 h-2 bg-purple-400 rounded-full"></span>
                    </div>
                </div>
            `;
            container.appendChild(div);
            container.scrollTop = container.scrollHeight;
        }
        
        function hideTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) indicator.remove();
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
