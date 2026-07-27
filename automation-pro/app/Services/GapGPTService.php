<?php
/**
 * GapGPT AI Service
 * Enterprise HR Automation System v2.0
 * 
 * Integration with GapGPT.app API for AI-powered insights
 */

namespace App\Services;

class GapGPTService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.gapgpt.app/v1';
    private string $model = 'gapgpt-4';
    
    public function __construct()
    {
        $this->apiKey = getenv('GAPGPT_API_KEY') ?: '';
    }
    
    /**
     * Set API key
     */
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }
    
    /**
     * Set model
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }
    
    /**
     * Send chat completion request
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'کلید API هوش مصنوعی تنظیم نشده است'
            ];
        }
        
        $url = $this->baseUrl . '/chat/completions';
        
        $payload = array_merge([
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000,
            'language' => 'fa'
        ], $options);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'خطا در ارتباط با سرویس هوش مصنوعی: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            $data = json_decode($response, true);
            return [
                'success' => false,
                'error' => $data['error']['message'] ?? 'خطا در پردازش درخواست'
            ];
        }
        
        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'response' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => $data['usage'] ?? [],
            'raw' => $data
        ];
    }
    
    /**
     * Analyze employee resignation risk
     */
    public function analyzeResignationRisk(array $employeeData): array
    {
        $prompt = $this->buildResignationRiskPrompt($employeeData);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک متخصص منابع انسانی با تجربه در تحلیل رفتار کارکنان هستید. وظیفه شما تحلیل احتمال ترک خدمت کارکنان بر اساس داده‌های ارائه شده است.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        return $this->chat($messages, ['temperature' => 0.5]);
    }
    
    /**
     * Summarize resume/CV
     */
    public function summarizeResume(string $resumeText): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک متخصص جذب و استخدام هستید. خلاصه‌ای جامع و ساختاریافته از رزومه ارائه دهید.'
            ],
            [
                'role' => 'user',
                'content' => "لطفاً این رزومه را تحلیل و خلاصه کنید:\n\n{$resumeText}"
            ]
        ];
        
        return $this->chat($messages, ['max_tokens' => 1500]);
    }
    
    /**
     * Generate performance review
     */
    public function generatePerformanceReview(array $performanceData): array
    {
        $prompt = $this->buildPerformanceReviewPrompt($performanceData);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک مدیر منابع انسانی باتجربه هستید که ارزیابی عملکرد کارکنان را انجام می‌دهید.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        return $this->chat($messages, ['temperature' => 0.6]);
    }
    
    /**
     * Generate job description
     */
    public function generateJobDescription(array $jobData): array
    {
        $prompt = $this->buildJobDescriptionPrompt($jobData);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک متخصص جذب و استخدام هستید که شرح شغل حرفه‌ای می‌نویسید.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        return $this->chat($messages, ['max_tokens' => 2500]);
    }
    
    /**
     * Analyze employee sentiment from feedback
     */
    public function analyzeSentiment(string $feedback): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'تحلیل احساسات بازخورد کارکنان و ارائه بینش‌های عملی.'
            ],
            [
                'role' => 'user',
                'content' => "این بازخورد را تحلیل کن و احساسات، نقاط قوت و پیشنهادات بهبود را مشخص کن:\n\n{$feedback}"
            ]
        ];
        
        return $this->chat($messages, ['temperature' => 0.4]);
    }
    
    /**
     * Generate training recommendations
     */
    public function generateTrainingRecommendations(array $employeeSkills, array $jobRequirements): array
    {
        $prompt = $this->buildTrainingPrompt($employeeSkills, $jobRequirements);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک مشاور توسعه و آموزش کارکنان هستید.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];
        
        return $this->chat($messages, ['max_tokens' => 2000]);
    }
    
    /**
     * Answer HR policy questions
     */
    public function answerHRPolicyQuestion(string $question, array $companyPolicies = []): array
    {
        $context = !empty($companyPolicies) 
            ? "سیاست‌های شرکت:\n" . json_encode($companyPolicies, JSON_UNESCAPED_UNICODE) . "\n\n" 
            : '';
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'شما یک متخصص منابع انسانی هستید که به سوالات مربوط به قوانین کار و سیاست‌های شرکت پاسخ می‌دهید.'
            ],
            [
                'role' => 'user',
                'content' => "{$context}سوال: {$question}"
            ]
        ];
        
        return $this->chat($messages, ['temperature' => 0.3]);
    }
    
    /**
     * Build resignation risk prompt
     */
    private function buildResignationRiskPrompt(array $data): string
    {
        return "لطفاً احتمال ترک خدمت این کارمند را تحلیل کنید:\n\n" .
               "اطلاعات کارمند:\n" .
               "- نام: {$data['name']}\n" .
               "- سابقه کار: {$data['tenure_years']} سال\n" .
               "- آخرین ارزیابی عملکرد: {$data['last_performance_score']}/100\n" .
               "- تعداد روزهای غیبت در ۶ ماه اخیر: {$data['absence_days']}\n" .
               "- تعداد تاخیرها: {$data['late_count']}\n" .
               "- حقوق نسبت به بازار: " . ($data['salary_vs_market'] ?? 'نامشخص') . "\n" .
               "- رضایت شغلی اعلام شده: {$data['satisfaction_score']}/10\n" .
               "- تعداد ارتقاء در ۲ سال اخیر: {$data['promotions_count']}\n" .
               "- فاصله از آخرین افزایش حقوق: {$data['months_since_raise']} ماه\n\n" .
               "لطفاً:\n" .
               "۱. ریسک ترک خدمت را (کم، متوسط، زیاد) مشخص کنید\n" .
               "۲. عوامل اصلی خطر را شناسایی کنید\n" .
               "۳. پیشنهاداتی برای حفظ این کارمند ارائه دهید";
    }
    
    /**
     * Build performance review prompt
     */
    private function buildPerformanceReviewPrompt(array $data): string
    {
        return "لطفاً ارزیابی عملکرد زیر را تحلیل و گزارش جامعی ارائه دهید:\n\n" .
               "اطلاعات کارمند: {$data['employee_name']}\n" .
               "دوره ارزیابی: {$data['period']}\n" .
               "امتیاز کلی: {$data['overall_score']}/100\n\n" .
               "شاخص‌های کلیدی:\n" .
               json_encode($data['kpis'], JSON_UNESCAPED_UNICODE) . "\n\n" .
               "بازخوردها:\n" .
               "- مدیر مستقیم: {$data['manager_feedback']}\n" .
               "- همکاران: {$data['peer_feedback']}\n" .
               "- خودارزیابی: {$data['self_assessment']}\n\n" .
               "لطفاً:\n" .
               "۱. نقاط قوت اصلی را مشخص کنید\n" .
               "۲. زمینه‌های نیاز به بهبود را شناسایی کنید\n" .
               "۳. اهداف پیشنهادی برای دوره بعد را تعیین کنید\n" .
               "۴. توصیه‌هایی برای توسعه حرفه‌ای ارائه دهید";
    }
    
    /**
     * Build job description prompt
     */
    private function buildJobDescriptionPrompt(array $data): string
    {
        return "لطفاً یک شرح شغل حرفه‌ای و کامل برای موقعیت شغلی زیر بنویسید:\n\n" .
               "عنوان شغل: {$data['title']}\n" .
               "واحد سازمانی: {$data['department']}\n" .
               "گزارش‌دهی به: {$data['reports_to']}\n" .
               "موقعیت مکانی: {$data['location']}\n" .
               "نوع قرارداد: {$data['contract_type']}\n\n" .
               "مسئولیت‌های کلیدی:\n" .
               implode("\n", array_map(fn($r) => "- {$r}", $data['responsibilities'])) . "\n\n" .
               "شرایط احراز:\n" .
               "- تحصیلات: {$data['education']}\n" .
               "- سابقه کار: {$data['experience_years']} سال\n" .
               "- مهارت‌های فنی: " . implode('، ', $data['technical_skills']) . "\n" .
               "- مهارت‌های نرم: " . implode('، ', $data['soft_skills']) . "\n\n" .
               "لطفاً شرح شغل را شامل بخش‌های زیر بنویسید:\n" .
               "۱. خلاصه موقعیت\n" .
               "۲. مسئولیت‌های اصلی\n" .
               "۳. شرایط احراز\n" .
               "۴. مزایای شغل\n" .
               "۵. معیارهای موفقیت";
    }
    
    /**
     * Build training recommendations prompt
     */
    private function buildTrainingPrompt(array $skills, array $requirements): string
    {
        return "لطفاً برنامه آموزشی پیشنهادی برای پر کردن شکاف مهارتی ارائه دهید:\n\n" .
               "مهارت‌های فعلی کارمند:\n" .
               json_encode($skills, JSON_UNESCAPED_UNICODE) . "\n\n" .
               "مهارت‌های مورد نیاز برای شغل:\n" .
               json_encode($requirements, JSON_UNESCAPED_UNICODE) . "\n\n" .
               "لطفاً:\n" .
               "۱. شکاف‌های مهارتی اصلی را شناسایی کنید\n" .
               "۲. دوره‌های آموزشی پیشنهادی را مشخص کنید\n" .
               "۳. اولویت‌بندی آموزش‌ها را انجام دهید\n" .
               "۴. زمان تخمینی برای هر دوره را مشخص کنید\n" .
               "۵. روش‌های یادگیری مناسب (آنلاین، حضوری، منتورینگ) را پیشنهاد دهید";
    }
    
    /**
     * Check if service is available
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }
}
