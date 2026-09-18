<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Support\SafeSeed;
use App\Support\SiteCopy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedSettings();
        $this->seedNavigation();
        $this->seedPages();
        $this->seedCatalog();
        $this->seedPartners();
        $this->call(PricingSeeder::class);
    }

    private function seedUsers(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@alwinco.ir');
        $password = env('ADMIN_PASSWORD', 'ChangeMe!Alwin2026');
        $user = SafeSeed::missing(User::class, ['email' => $email], [
            'name' => 'مدیر آلوین',
            'password' => $password,
            'role' => User::ROLE_SUPER_ADMIN,
            'is_active' => true,
        ]);
        $this->command?->info($user->wasRecentlyCreated ? 'Admin created: '.$email : 'Admin already exists: '.$email);
    }

    protected function seedSettings(): void
    {
        $rows = [
            ['group' => 'contact', 'key' => 'email', 'label' => 'ایمیل', 'type' => 'text', 'value' => 'info@alwinco.ir'],
            ['group' => 'contact', 'key' => 'phone', 'label' => 'تلفن', 'type' => 'text', 'value' => '09909777090'],
            ['group' => 'contact', 'key' => 'phone_display', 'label' => 'نمایش تلفن', 'type' => 'text', 'value' => '09909777090'],
            ['group' => 'contact', 'key' => 'address', 'label' => 'آدرس', 'type' => 'textarea', 'value' => 'بزرگراه اشرفی اصفهانی، ابتدای جلال آل احمد، پلاک ۱۸۲، ساختمان آوند، طبقه ۵'],
            ['group' => 'brand', 'key' => 'site_name', 'label' => 'نام سایت', 'type' => 'text', 'value' => 'ALWIN'],
            ['group' => 'brand', 'key' => 'tagline', 'label' => 'شعار فوتر', 'type' => 'textarea', 'value' => 'آلوین — تولیدکننده و نصاب پنجره و درب دوجداره UPVC با بیش از ۱۵ سال سابقه'],
            ['group' => 'brand', 'key' => 'cta_label', 'label' => 'متن دکمه محاسبه قیمت', 'type' => 'text', 'value' => 'محاسبه قیمت'],
            ['group' => 'video', 'key' => 'intro_src', 'label' => 'مسیر ویدیو معرفی', 'type' => 'text', 'value' => 'assets/Video/intro.mp4'],
            ['group' => 'social', 'key' => 'instagram', 'label' => 'اینستاگرام', 'type' => 'text', 'value' => '/contact'],
            ['group' => 'social', 'key' => 'telegram', 'label' => 'تلگرام', 'type' => 'text', 'value' => '/contact'],
            ['group' => 'social', 'key' => 'whatsapp', 'label' => 'واتساپ', 'type' => 'text', 'value' => '/contact'],
            ['group' => 'social', 'key' => 'aparat', 'label' => 'آپارات', 'type' => 'text', 'value' => '/contact'],
            ['group' => 'social', 'key' => 'linkedin', 'label' => 'لینکدین', 'type' => 'text', 'value' => '/contact'],
            ['group' => 'footer', 'key' => 'copyright', 'label' => 'کپی‌رایت', 'type' => 'text', 'value' => '© ۲۰۲۶ آلوین. تمامی حقوق محفوظ است.'],
            ['group' => 'footer', 'key' => 'credit', 'label' => 'اعتبار طراحی', 'type' => 'text', 'value' => 'طراحی و توسعه توسط تیم نوین کدرز'],
        ];
        foreach ($rows as $row) {
            $this->ensureSetting($row);
        }
        foreach (SiteCopy::extraSettings() as $row) {
            $this->ensureSetting($row);
        }
        foreach (SiteCopy::calculator() as $key => $meta) {
            $this->ensureSetting([
                'group' => 'calculator',
                'key' => $key,
                'label' => $meta['label'],
                'type' => $meta['type'],
                'value' => $meta['value'],
            ]);
        }
    }

    /**
     * @param  array{group: string, key: string, label: string, type: string, value: string}  $row
     */
    private function ensureSetting(array $row): void
    {
        $setting = Setting::query()->firstOrNew(['key' => $row['key']]);
        $setting->group = $row['group'];
        $setting->label = $row['label'];
        $setting->type = $row['type'];
        if (! $setting->exists) {
            $setting->value = $row['value'];
        }
        $setting->save();
    }

    private function seedNavigation(): void
    {
        $header = [
            ['خانه', '/'],
            ['محصولات', '/products'],
            ['پروژه‌ها', '/projects'],
            ['مقالات', '/articles'],
            ['درباره ما', '/about'],
            ['تماس', '/contact'],
        ];
        foreach ($header as $i => [$label, $url]) {
            SafeSeed::missing(NavigationItem::class, ['location' => 'header', 'url' => $url], [
                'label' => $label,
                'sort_order' => $i,
                'is_active' => true,
            ]);
            SafeSeed::missing(NavigationItem::class, ['location' => 'footer', 'url' => $url], [
                'label' => $label,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    protected function seedPages(): void
    {
        $pages = [
            'home' => ['خانه', '/', 'ALWIN | پنجره دوجداره UPVC', 'ALWIN — تولید و نصب پنجره دوجداره UPVC'],
            'about' => ['درباره ما', '/about', 'درباره ما | ALWIN', 'درباره شرکت بهینه گستر نمای آفتاب (آلوین)'],
            'services' => ['محصولات', '/products', 'محصولات | ALWIN', 'محصولات پنجره و درب UPVC آلوین'],
            'portfolio' => ['پروژه‌ها', '/projects', 'پروژه‌ها | ALWIN', 'پروژه‌های اجرایی آلوین'],
            'contact' => ['تماس', '/contact', 'تماس با ما | ALWIN', 'تماس با آلوین پنجره'],
            'articles' => ['مقالات', '/articles', 'مقالات | ALWIN', 'راهنماها و نکات تخصصی درباره پنجره دوجداره UPVC'],
            'project' => ['جزئیات پروژه', '/projects', 'پروژه | ALWIN', 'جزئیات پروژه آلوین'],
        ];
        foreach ($pages as $key => [$title, $path, $seoTitle, $seoDesc]) {
            SafeSeed::missing(Page::class, ['key' => $key], [
                'title' => $title,
                'path' => $path,
                'seo_title' => $seoTitle,
                'seo_description' => $seoDesc,
            ]);
        }

        $home = Page::query()->where('key', 'home')->first();
        $this->blocks($home, [
            ['hero_title', 'html', 'عنوان هیرو', 'تولید و نصب پنجره دوجداره UPVC<br>با کیفیت و قیمت مناسب'],
            ['hero_text', 'textarea', 'متن هیرو', 'پیشرو در تولید و نصب پنجره و درب دوجداره UPVC با استانداردهای اروپایی، قیمت شفاف و مشاوره رایگان.'],
            ['hero_stat_1_number', 'text', 'آمار ۱', '+۱۵'],
            ['hero_stat_1_label', 'text', 'برچسب آمار ۱', 'سال تجربه در تولید و نصب'],
            ['hero_stat_2_number', 'text', 'آمار ۲', '+۵۰۰'],
            ['hero_stat_2_label', 'text', 'برچسب آمار ۲', 'پروژه موفق در سراسر کشور'],
            ['about_title', 'text', 'عنوان درباره', 'ALWIN'],
            ['about_text', 'textarea', 'متن درباره', 'با بیش از ۱۵ سال تجربه در تولید، نصب و خدمات پس از فروش پنجره و درب UPVC، ALWIN همراه شماست از انتخاب مدل تا نصب نهایی. پروفیل‌های معتبر، شیشه‌های باکیفیت و تیم نصب حرفه‌ای — همه در یک آدرس.'],
            ['about_button', 'text', 'دکمه درباره', 'بیشتر بدانید'],
            ['clients_title', 'text', 'عنوان مشتریان', 'مشتریان ما: همراهی با برندهای معتبر و صدها خانواده راضی در سراسر ایران'],
            ['clients_text', 'textarea', 'متن مشتریان', 'کیفیت محصول و خدمات ALWIN مورد اعتماد سازندگان، معماران و مالکین واحدهای مسکونی و تجاری است. ما به کیفیت متعهد هستیم و آن را در هر پروژه ثابت می‌کنیم.'],
            ['cta_title', 'text', 'عنوان بنر محاسبه قیمت', 'قیمت پنجره‌تان را همین حالا ببینید'],
            ['cta_kicker', 'text', 'برچسب بنر محاسبه قیمت', 'برآورد آنلاین و رایگان'],
            ['cta_hint', 'textarea', 'توضیح بنر محاسبه قیمت', 'ابعاد را وارد کنید؛ نتیجه را در لحظه ببینید'],
            ['work_title', 'text', 'عنوان پروژه‌های خانه', 'پروژه‌های اجرا شده'],
            ['work_text', 'textarea', 'متن پروژه‌های خانه', 'نمونه‌ای از پروژه‌های نصب پنجره و درب UPVC در تهران و شهرهای اطراف'],
            ['work_all_button', 'text', 'دکمه همه پروژه‌ها', 'مشاهده همه پروژه‌ها'],
            ['products_title', 'text', 'عنوان محصولات خانه', 'محصولات'],
            ['principles_title', 'html', 'اصول', 'کیفیت، دقت و <br> قیمت منصفانه — <span>سه اصل</span> <span>ALWIN</span> در <span>هر پروژه</span>'],
            ['clients_marquee_title', 'text', 'عنوان نوار مشتریان', 'برخی از مشتریان ما'],
            ['hero_video_name', 'text', 'نام ویدیو معرفی', 'ویدیو معرفی آلوین پنجره'],
            ['intro_pause_label', 'text', 'برچسب توقف ویدیو', 'توقف ویدیو'],
            ['intro_play_label', 'text', 'برچسب پخش ویدیو', 'پخش ویدیو'],
        ]);
        if ($home) {
            $this->migrateHomeCopy($home);
        }

        $about = Page::query()->where('key', 'about')->first();
        $this->blocks($about, [
            ['legal_name', 'text', 'نام حقوقی', 'شرکت بهینه گستر نمای آفتاب'],
            ['hero_title', 'text', 'عنوان', 'از ثبت ۱۳۹۲ تا تولید حرفه‌ای در و پنجره'],
            ['hero_lead', 'textarea', 'متن مقدمه', 'آلوین با مدرن‌ترین ماشین‌آلات روز دنیا، تولید در و پنجره‌های UPVC، آلومینیوم و انواع توری را آغاز کرد — و مسیر کیفیت را با نمایندگی برندهای معتبر ادامه داد.'],
            ['cta_contact', 'text', 'دکمه تماس', 'تماس با آلوین'],
            ['cta_story', 'text', 'دکمه داستان', 'داستان ما'],
            ['story_title', 'text', 'عنوان داستان', 'بهینه گستر نمای آفتاب؛ نام تجاری آلوین'],
            ['story_html', 'html', 'متن داستان', '<p>شرکت <strong>بهینه گستر نمای آفتاب</strong> با نام تجاری <strong>آلوین (ALWIN)</strong> در سال ۱۳۹۲ با شماره ثبت <strong>۴۴۶۴۱۹</strong> ثبت شد و با بهره‌گیری از مدرن‌ترین ماشین‌آلات روز دنیا، فعالیت خود را در زمینه تولید <strong>در و پنجره‌های UPVC، آلومینیوم و انواع توری</strong> آغاز نمود.</p><p>این مجموعه در راستای توسعه و پیشبرد اهداف خود موفق به اخذ نمایندگی از شرکت <strong>وین‌تک</strong> با درجه کیفی <strong>A</strong> و همچنین اخذ نمایندگی از شرکت <strong>ویستابست</strong> از سال ۹۹ گردیده است.</p><p>امید است با استعانت و یاری از پروردگار یکتا و تأییدات الهی، با ساختار کارگروهی، تکیه بر سرمایه‌های اجتماعی، بهره‌گیری از تجربه مدیران و پیاده‌سازی استراتژی‌ها در عمل، بتوانیم بیش از پیش رضایت مشتریان خود را فراهم نماییم.</p>'],
            ['fact_1_value', 'text', 'آمار ۱', '۱۳۹۲'],
            ['fact_1_label', 'text', 'برچسب آمار ۱', 'سال تأسیس'],
            ['fact_2_value', 'text', 'آمار ۲', '۴۴۶۴۱۹'],
            ['fact_2_label', 'text', 'برچسب آمار ۲', 'شماره ثبت'],
            ['fact_3_value', 'text', 'آمار ۳', '۱۰ سال'],
            ['fact_3_label', 'text', 'برچسب آمار ۳', 'گارانتی پروفیل'],
            ['story_eyebrow', 'text', 'برچسب داستان', 'هویت شرکت'],
            ['chip_1_title', 'text', 'کارت ۱ عنوان', 'تولید تخصصی'],
            ['chip_1_text', 'text', 'کارت ۱ متن', 'در و پنجره UPVC، آلومینیوم و انواع توری با ماشین‌آلات مدرن'],
            ['chip_2_title', 'text', 'کارت ۲ عنوان', 'وین‌تک درجه A'],
            ['chip_2_text', 'text', 'کارت ۲ متن', 'اخذ نمایندگی شرکت وین‌تک با بالاترین درجه کیفی'],
            ['chip_3_title', 'text', 'کارت ۳ عنوان', 'ویستابست از ۹۹'],
            ['chip_3_text', 'text', 'کارت ۳ متن', 'نمایندگی رسمی ویستابست در غرب تهران و کرج'],
            ['chip_4_title', 'text', 'کارت ۴ عنوان', 'ضمانت و خدمات'],
            ['chip_4_text', 'text', 'کارت ۴ متن', 'محصولات همراه با گارانتی و خدمات پس از فروش'],
            ['path_eyebrow', 'text', 'برچسب مسیر رشد', 'مسیر رشد'],
            ['path_title', 'text', 'عنوان مسیر رشد', 'از تأسیس تا نمایندگی‌های رسمی'],
            ['path_lead', 'textarea', 'متن مسیر رشد', 'نقاط کلیدی در شکل‌گیری هویت صنعتی آلوین، بر اساس مسیر واقعی شرکت.'],
            ['path_1_year', 'text', 'مسیر ۱ سال', '۱۳۹۲'],
            ['path_1_title', 'text', 'مسیر ۱ عنوان', 'ثبت شرکت و آغاز تولید'],
            ['path_1_text', 'textarea', 'مسیر ۱ متن', 'ثبت شرکت بهینه گستر نمای آفتاب با شماره ۴۴۶۴۱۹ و شروع تولید در و پنجره UPVC، آلومینیوم و توری.'],
            ['path_2_year', 'text', 'مسیر ۲ سال', 'وین‌تک'],
            ['path_2_title', 'text', 'مسیر ۲ عنوان', 'نمایندگی درجه کیفی A'],
            ['path_2_text', 'textarea', 'مسیر ۲ متن', 'اخذ نمایندگی شرکت وین‌تک با درجه کیفی A در مسیر توسعه و ارتقای استاندارد تولید.'],
            ['path_3_year', 'text', 'مسیر ۳ سال', 'از سال ۹۹'],
            ['path_3_title', 'text', 'مسیر ۳ عنوان', 'نمایندگی ویستابست'],
            ['path_3_text', 'textarea', 'مسیر ۳ متن', 'اخذ نمایندگی ویستابست و سپس نمایندگی رسمی در غرب تهران و کرج، با ضمانت و خدمات پس از فروش.'],
            ['dealers_eyebrow', 'text', 'برچسب نمایندگی‌ها', 'شراکت‌های رسمی'],
            ['dealers_title', 'text', 'عنوان نمایندگی‌ها', 'نمایندگی برندهای معتبر پروفیل'],
            ['dealers_lead', 'textarea', 'متن نمایندگی‌ها', 'آلوین مسیر کیفیت را با نمایندگی برندهایی طی کرده که استاندارد ساخت و نصب را جدی می‌گیرند.'],
            ['dealer_1_tag', 'text', 'نمایندگی ۱ برچسب', 'نمایندگی'],
            ['dealer_1_name', 'text', 'نمایندگی ۱ نام', 'وین‌تک — درجه A'],
            ['dealer_1_text', 'textarea', 'نمایندگی ۱ متن', 'اخذ نمایندگی شرکت وین‌تک با درجه کیفی A، در راستای توسعه و پیشبرد اهداف تولیدی مجموعه.'],
            ['dealer_2_tag', 'text', 'نمایندگی ۲ برچسب', 'نمایندگی رسمی'],
            ['dealer_2_name', 'text', 'نمایندگی ۲ نام', 'ویستابست — غرب تهران و کرج'],
            ['dealer_2_text', 'textarea', 'نمایندگی ۲ متن', 'نمایندگی رسمی شرکت ویستابست در غرب تهران و کرج؛ محصولات مطابق استانداردهای فنی ویستابست، همراه با ضمانت و خدمات پس از فروش.'],
            ['vista_eyebrow', 'text', 'برچسب ویستابست', 'چرا ویستابست؟'],
            ['vista_title', 'text', 'عنوان ویستابست', 'کیفیت پروفیل‌ها و پنجره‌های ویستابست'],
            ['vista_intro', 'textarea', 'متن ویستابست', 'در راستای دیدگاه کیفیت‌محور شرکت ویستابست، حمایت از حقوق مصرف‌کنندگان و آشنایی تولیدکنندگان پنجره با استانداردهای ساخت و نصب، این شرکت دفترچه الزامات فنی ویستابست را منتشر نموده است.'],
            ['vista_points', 'textarea', 'نکات ویستابست (هر خط یک مورد)', "بهره‌گیری از دانش فنی شرکت‌های معتبر بین‌المللی در تأمین مواد اولیه و استفاده از ماشین‌آلات مدرن.\nتولید پروفیل‌هایی با ماندگاری و کیفیت بالا در شرایط آب‌وهوایی مختلف ایران.\nنظارت واحد کنترل کیفیت بر فرآیند تولید و انجام آزمون‌های تخصصی در آزمایشگاه مجهز.\nتضمین کیفیت محصولات مطابق با استانداردهای ملی و بین‌المللی."],
            ['warranty_eyebrow', 'text', 'برچسب گارانتی', 'تعهد پس از فروش'],
            ['warranty_title', 'text', 'عنوان گارانتی', 'گارانتی و خدمات پس از فروش'],
            ['warranty_lead', 'textarea', 'متن گارانتی', 'پوشش گارانتی آلوین بر اساس تعهدات اعلام‌شده برای پروفیل، یراق، شیشه و رگلاژ.'],
            ['warranty_1_years', 'text', 'گارانتی ۱ عدد', '۱۰'],
            ['warranty_1_label', 'text', 'گارانتی ۱ برچسب', 'گارانتی پروفیل'],
            ['warranty_2_years', 'text', 'گارانتی ۲ عدد', '۵'],
            ['warranty_2_label', 'text', 'گارانتی ۲ برچسب', 'یراق‌آلات'],
            ['warranty_3_years', 'text', 'گارانتی ۳ عدد', '۵'],
            ['warranty_3_label', 'text', 'گارانتی ۳ برچسب', 'شیشه'],
            ['warranty_4_years', 'text', 'گارانتی ۴ عدد', '۱'],
            ['warranty_4_label', 'text', 'گارانتی ۴ برچسب', 'رگلاژ و آب‌بندی مجدد'],
            ['warranty_unit', 'text', 'واحد گارانتی', 'سال'],
            ['locations_eyebrow', 'text', 'برچسب مکان‌ها', 'حضور ما'],
            ['locations_title', 'text', 'عنوان مکان‌ها', 'دفتر مرکزی و کارخانه'],
            ['office_type', 'text', 'برچسب دفتر', 'دفتر مرکزی'],
            ['office_place_title', 'text', 'عنوان دفتر', 'تهران — ساختمان آوند'],
            ['office_hotline_label', 'text', 'برچسب خط ویژه دفتر', 'خط ویژه سفارشات و مشاوره'],
            ['factory_type', 'text', 'برچسب کارخانه', 'کارخانه'],
            ['callback_eyebrow', 'text', 'برچسب فرم تماس', 'درخواست تماس'],
            ['callback_title', 'text', 'عنوان فرم تماس', 'آماده همکاری با پروژه‌ی بعدی شما هستیم'],
            ['callback_text', 'textarea', 'متن فرم تماس', 'نام و شماره را بگذارید؛ کارشناس آلوین برای مشاوره، بازدید یا ثبت سفارش با شما تماس می‌گیرد.'],
            ['callback_name_label', 'text', 'برچسب نام فرم', 'نام و نام خانوادگی'],
            ['callback_phone_label', 'text', 'برچسب تلفن فرم', 'شماره تماس'],
            ['callback_message_label', 'text', 'برچسب پیام فرم', 'توضیح سفارش'],
            ['callback_message_optional', 'text', 'برچسب اختیاری', '(اختیاری)'],
            ['callback_message_placeholder', 'text', 'نمونه پیام فرم', 'نوع پنجره، ابعاد تقریبی یا زمان مناسب تماس…'],
            ['callback_submit', 'text', 'دکمه فرم تماس', 'ثبت درخواست تماس'],
            ['callback_phone_placeholder', 'text', 'نمونه تلفن فرم', '09xxxxxxxxx'],
            ['callback_err_name', 'text', 'خطای نام فرم', 'نام را کامل وارد کنید.'],
            ['callback_err_phone', 'text', 'خطای تلفن فرم', 'شماره تماس معتبر وارد کنید.'],
            ['callback_err_fix', 'text', 'خطای اصلاح فرم', 'لطفاً موارد مشخص‌شده را اصلاح کنید.'],
            ['callback_submitting', 'text', 'در حال ثبت فرم', 'در حال ثبت...'],
            ['callback_success', 'text', 'موفقیت فرم تماس', 'درخواست ثبت شد. کارشناسان آلوین به‌زودی تماس می‌گیرند.'],
            ['callback_error', 'text', 'خطای ثبت فرم ({phone} جایگزین می‌شود)', 'ثبت نشد. لطفاً دوباره تلاش کنید یا با {phone} تماس بگیرید.'],
        ]);
        if ($about) {
            $this->migrateAboutCopy($about);
        }

        $contact = Page::query()->where('key', 'contact')->first();
        $this->blocks($contact, [
            ['hero_title', 'text', 'عنوان', 'مشاوره مستقیم با تیم تولید و نصب'],
            ['hero_lead', 'textarea', 'متن مقدمه', 'برای سفارش، بازدید فنی یا دریافت قیمت شفاف، از خط ویژه یا فرم زیر با آلوین در ارتباط باشید.'],
            ['cta_call', 'text', 'دکمه تماس', 'تماس با خط ویژه'],
            ['cta_form', 'text', 'دکمه فرم', 'ارسال پیام'],
            ['office_title', 'text', 'عنوان دفتر', 'آدرس مراجعه حضوری'],
            ['hotline_label', 'text', 'برچسب خط ویژه', 'خط ویژه سفارشات و مشاوره'],
            ['hotline_button', 'text', 'دکمه تماس فوری', 'همین حالا تماس بگیرید'],
            ['aside_eyebrow', 'text', 'برچسب راه‌های ارتباطی', 'راه‌های ارتباطی'],
            ['aside_title', 'text', 'عنوان راه‌های ارتباطی', 'دفتر مرکزی و مسیرهای تماس'],
            ['aside_text', 'textarea', 'متن راه‌های ارتباطی', 'اطلاعات زیر از دفتر مرکزی آلوین است. برای پیگیری سریع‌تر، خط ویژه سفارشات و مشاوره را در اولویت قرار دهید.'],
            ['channel_hotline', 'text', 'برچسب کانال خط ویژه', 'خط ویژه'],
            ['channel_office_phones', 'text', 'برچسب تلفن‌های دفتر', 'تلفن‌های دفتر'],
            ['channel_email', 'text', 'برچسب ایمیل', 'ایمیل'],
            ['channel_website', 'text', 'برچسب وب‌سایت', 'وب‌سایت'],
            ['channel_address', 'text', 'برچسب آدرس', 'آدرس دفتر مرکزی'],
            ['form_title', 'text', 'عنوان فرم', 'فرم تماس'],
            ['form_lead', 'textarea', 'متن فرم', 'پیام خود را بنویسید؛ کارشناسان آلوین در کوتاه‌ترین زمان پاسخ می‌دهند.'],
            ['form_name_label', 'text', 'برچسب نام', 'نام و نام خانوادگی'],
            ['form_phone_label', 'text', 'برچسب تلفن', 'شماره تماس'],
            ['form_email_label', 'text', 'برچسب ایمیل فرم', 'ایمیل'],
            ['form_subject_label', 'text', 'برچسب موضوع', 'موضوع'],
            ['form_subject_placeholder', 'text', 'انتخاب موضوع', 'انتخاب کنید'],
            ['form_subject_quote', 'text', 'موضوع قیمت', 'درخواست قیمت / مشاوره'],
            ['form_subject_visit', 'text', 'موضوع بازدید', 'بازدید فنی و اندازه‌گیری'],
            ['form_subject_order', 'text', 'موضوع سفارش', 'پیگیری سفارش'],
            ['form_subject_support', 'text', 'موضوع پشتیبانی', 'پشتیبانی پس از نصب'],
            ['form_subject_other', 'text', 'موضوع سایر', 'سایر'],
            ['form_message_label', 'text', 'برچسب پیام', 'پیام'],
            ['form_message_placeholder', 'text', 'نمونه پیام', 'شرح کوتاه درخواست شما…'],
            ['form_submit', 'text', 'دکمه ارسال', 'ارسال پیام'],
            ['office_eyebrow', 'text', 'برچسب دفتر مراجعه', 'دفتر مرکزی'],
            ['map_link_label', 'text', 'لینک نقشه', 'مسیریابی در نقشه'],
            ['form_phone_placeholder', 'text', 'نمونه تلفن فرم', '09xxxxxxxxx'],
            ['form_err_name', 'text', 'خطای نام', 'نام را کامل وارد کنید.'],
            ['form_err_phone', 'text', 'خطای تلفن', 'شماره تماس معتبر وارد کنید.'],
            ['form_err_email', 'text', 'خطای ایمیل', 'ایمیل معتبر نیست.'],
            ['form_err_subject', 'text', 'خطای موضوع', 'موضوع را انتخاب کنید.'],
            ['form_err_message', 'text', 'خطای پیام', 'پیام حداقل ۱۰ کاراکتر باشد.'],
            ['form_err_fix', 'text', 'خطای اصلاح فرم', 'لطفاً موارد مشخص‌شده را اصلاح کنید.'],
            ['form_submitting', 'text', 'در حال ارسال', 'در حال ثبت...'],
            ['form_success', 'text', 'موفقیت فرم تماس', 'پیام شما ثبت شد. کارشناسان آلوین به‌زودی پاسخ می‌دهند.'],
            ['form_error', 'text', 'خطای ثبت فرم', 'ثبت نشد. لطفاً دوباره تلاش کنید.'],
        ]);

        $services = Page::query()->where('key', 'services')->first();
        $this->blocks($services, [
            ['hero_title', 'text', 'عنوان صفحه محصولات', 'محصولات آلوین'],
            ['hero_lead', 'textarea', 'توضیح محصولات', 'تمام مدل‌های پنجره و درب دوجداره UPVC و توری‌های پنجره را در یک‌جا ببینید — از پنجره‌های لولایی، کشویی و فرانسوی تا توری‌های پلیسه‌ای و رولینگ. برای هر مدل جزئیات را ببینید و استعلام بگیرید.'],
            ['stat_1_number', 'text', 'آمار تجربه', '۱۵+'],
            ['stat_1_label', 'text', 'برچسب آمار تجربه', 'سال تجربه تولید'],
            ['stat_2_number', 'text', 'آمار پروژه', '۵۰۰+'],
            ['stat_2_label', 'text', 'برچسب آمار پروژه', 'پروژه نصب‌شده'],
            ['stat_products_label', 'text', 'برچسب شمارش محصول', 'محصول'],
            ['search_placeholder', 'text', 'جستجوی محصولات', 'جستجو در مدل‌ها، مثلاً «کشویی» و ...'],
            ['tab_all', 'text', 'تب همه', 'همه محصولات'],
            ['empty_text', 'textarea', 'متن خالی', 'موردی با این مشخصات پیدا نشد. جستجوی دیگری را امتحان کنید یا فیلترها را پاک کنید.'],
            ['load_more', 'text', 'نمایش بیشتر', 'نمایش محصولات بیشتر'],
            ['load_less', 'text', 'نمایش کمتر', 'نمایش محصولات کمتر'],
            ['count_template', 'text', 'شمارش نتایج ({shown} و {total})', 'نمایش {shown} از {total} محصول'],
            ['load_error', 'text', 'خطای بارگذاری', 'بارگذاری محصولات با خطا مواجه شد.'],
            ['trust_title', 'text', 'عنوان اعتماد', 'چرا آلوین؟'],
            ['trust_1_title', 'text', 'اعتماد ۱ عنوان', 'پروفیل معتبر اروپایی'],
            ['trust_1_text', 'textarea', 'اعتماد ۱ متن', 'تولید با پروفیل‌های ویستابست، وینتک، پلاس پن و وین پلاس با استاندارد اروپایی.'],
            ['trust_2_title', 'text', 'اعتماد ۲ عنوان', 'یراق‌آلات آلمانی و ترک'],
            ['trust_2_text', 'textarea', 'اعتماد ۲ متن', 'امکان انتخاب یراق‌آلات آلمانی یا ترک متناسب با بودجه و نیاز شما.'],
            ['trust_3_title', 'text', 'اعتماد ۳ عنوان', 'گارانتی و خدمات پس از فروش'],
            ['trust_3_text', 'textarea', 'اعتماد ۳ متن', 'پشتیبانی، گارانتی معتبر محصول و خدمات تعمیر و نگهداری در سراسر کشور.'],
            ['trust_4_title', 'text', 'اعتماد ۴ عنوان', 'بازدید و مشاوره رایگان'],
            ['trust_4_text', 'textarea', 'اعتماد ۴ متن', 'کارشناسان ما پیش از سفارش، رایگان بازدید و ابعاد دقیق را اندازه‌گیری می‌کنند.'],
        ]);

        $portfolio = Page::query()->where('key', 'portfolio')->first();
        $this->blocks($portfolio, [
            ['hero_title', 'text', 'عنوان پروژه‌ها', 'پروژه‌های اجرایی آلوین'],
            ['hero_lead', 'textarea', 'توضیح پروژه‌ها', 'نمونه‌ای از پروژه‌های نصب پنجره و درب UPVC، نمای کرتین وال، جام بالکن و شیشه دوجداره — اجرا شده با دقت مهندسی و استاندارد اروپایی در تهران و شهرهای اطراف.'],
            ['stat_1_number', 'text', 'آمار ۱', '+۵۰۰'],
            ['stat_1_label', 'text', 'برچسب آمار ۱', 'پروژه موفق'],
            ['stat_2_number', 'text', 'آمار ۲', '+۱۵'],
            ['stat_2_label', 'text', 'برچسب آمار ۲', 'سال تجربه'],
            ['stat_3_label', 'text', 'برچسب شمارش نمونه‌کار', 'نمونه‌کار برگزیده'],
            ['empty_text', 'textarea', 'متن خالی', 'پروژه‌ای در این دسته یافت نشد. دسته دیگری را انتخاب کنید.'],
            ['load_more', 'text', 'نمایش بیشتر', 'نمایش پروژه‌های بیشتر'],
            ['load_less', 'text', 'نمایش کمتر', 'نمایش پروژه‌های کمتر'],
            ['count_template', 'text', 'شمارش نتایج ({shown} و {total})', 'نمایش {shown} از {total} پروژه'],
            ['load_error', 'text', 'خطای بارگذاری', 'بارگذاری پروژه‌ها با خطا مواجه شد.'],
            ['filter_all', 'text', 'فیلتر همه', 'همه'],
            ['filter_upvc', 'text', 'فیلتر UPVC', 'پنجره UPVC'],
            ['filter_curtain', 'text', 'فیلتر کرتین وال', 'نمای کرتین وال'],
            ['filter_balcony', 'text', 'فیلتر جام بالکن', 'جام بالکن'],
            ['filter_glass', 'text', 'فیلتر شیشه', 'شیشه دوجداره'],
        ]);

        $articles = Page::query()->where('key', 'articles')->first();
        $this->blocks($articles, [
            ['hero_title', 'text', 'عنوان مقالات', 'مقالات آلوین'],
            ['hero_lead', 'textarea', 'توضیح مقالات', 'راهنماها و نکات تخصصی درباره پنجره دوجداره UPVC'],
            ['crumb_home', 'text', 'خرده نان خانه', 'خانه'],
            ['crumb_current', 'text', 'خرده نان فعلی', 'مقالات'],
            ['card_category', 'text', 'برچسب کارت', 'مقالات'],
            ['empty_text', 'text', 'متن خالی', 'مقاله‌ای منتشر نشده است.'],
            ['load_more', 'text', 'نمایش بیشتر', 'نمایش مقالات بیشتر'],
            ['load_less', 'text', 'نمایش کمتر', 'نمایش مقالات کمتر'],
            ['published_label', 'text', 'برچسب تاریخ', 'انتشار:'],
            ['author_label', 'text', 'برچسب نویسنده', 'نویسنده:'],
            ['back_label', 'text', 'بازگشت', 'بازگشت به مقالات'],
            ['related_title', 'text', 'عنوان مرتبط', 'مقالات مرتبط'],
        ]);

        $project = Page::query()->where('key', 'project')->first();
        $this->blocks($project, [
            ['crumb_home', 'text', 'خرده نان خانه', 'خانه'],
            ['crumb_projects', 'text', 'خرده نان پروژه‌ها', 'پروژه‌ها'],
            ['crumb_aria', 'text', 'برچسب مسیر صفحه', 'مسیر صفحه'],
            ['summary_title', 'text', 'عنوان شرح پروژه', 'شرح پروژه'],
            ['nav_prev', 'text', 'پروژه قبلی', 'پروژه قبلی'],
            ['nav_next', 'text', 'پروژه بعدی', 'پروژه بعدی'],
            ['nav_all', 'text', 'همه پروژه‌ها', 'همه پروژه‌ها'],
            ['cta_html', 'html', 'عنوان بنر محاسبه قیمت', 'محاسبه<br>قیمت'],
        ]);
    }

    private function blocks(?Page $page, array $defs): void
    {
        if (! $page) {
            return;
        }
        foreach ($defs as $i => [$key, $type, $label, $value]) {
            $block = PageBlock::query()->firstOrNew(['page_id' => $page->id, 'key' => $key]);
            $isNew = ! $block->exists;
            $block->type = $type;
            $block->label = $label;
            if ($isNew || $block->value === null || $block->value === '') {
                $block->value = $value;
            }
            if ($isNew) {
                $block->sort_order = $i;
            }
            $block->save();
        }
    }

    private function migrateHomeCopy(Page $home): void
    {
        $cta = PageBlock::query()->where('page_id', $home->id)->where('key', 'cta_title')->first();
        if ($cta && trim((string) $cta->value) === 'محاسبه قیمت') {
            $cta->value = 'قیمت پنجره‌تان را همین حالا ببینید';
            $cta->label = 'عنوان بنر محاسبه قیمت';
            $cta->save();
        }
        $kicker = PageBlock::query()->where('page_id', $home->id)->where('key', 'cta_kicker')->first();
        if ($kicker && trim((string) $kicker->value) === 'برآورد رایگان و بدون تعهد') {
            $kicker->value = 'برآورد آنلاین و رایگان';
            $kicker->save();
        }
        $hint = PageBlock::query()->where('page_id', $home->id)->where('key', 'cta_hint')->first();
        if ($hint && str_contains((string) $hint->value, 'دو دقیقه')) {
            $hint->value = 'ابعاد را وارد کنید؛ نتیجه را در لحظه ببینید';
            $hint->save();
        }
        $principles = PageBlock::query()->where('page_id', $home->id)->where('key', 'principles_title')->first();
        if ($principles && ! str_contains((string) $principles->value, '<span>')) {
            $principles->value = 'کیفیت، دقت و <br> قیمت منصفانه — <span>سه اصل</span> <span>ALWIN</span> در <span>هر پروژه</span>';
            $principles->save();
        }
    }

    private function migrateAboutCopy(Page $about): void
    {
        $story = PageBlock::query()->where('page_id', $about->id)->where('key', 'story_html')->first();
        if (! $story) {
            return;
        }
        $value = (string) $story->value;
        if ($value !== '' && ! str_contains($value, 'استعانت') && str_contains($value, 'ویستابست')) {
            $story->value = rtrim($value).'<p>امید است با استعانت و یاری از پروردگار یکتا و تأییدات الهی، با ساختار کارگروهی، تکیه بر سرمایه‌های اجتماعی، بهره‌گیری از تجربه مدیران و پیاده‌سازی استراتژی‌ها در عمل، بتوانیم بیش از پیش رضایت مشتریان خود را فراهم نماییم.</p>';
            $story->save();
        }
    }

    private function seedCatalog(): void
    {
        $root = base_path();
        $catWindow = SafeSeed::missing(ProductCategory::class, ['slug' => 'upvc-windows'], [
            'name' => 'درب و پنجره UPVC',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $catLace = SafeSeed::missing(ProductCategory::class, ['slug' => 'insect-screens'], [
            'name' => 'توری پنجره',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $productsPath = $root.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'products-data.json';
        if (! is_file($productsPath)) {
            $productsPath = $root.DIRECTORY_SEPARATOR.'products-data.json';
        }
        $data = is_file($productsPath) ? json_decode((string) file_get_contents($productsPath), true) : [];
        foreach ($data['products'] ?? [] as $i => $row) {
            $close = $this->mediaFromSite($row['images']['close'] ?? '', $row['images']['alt'] ?? $row['title']);
            $open = $this->mediaFromSite($row['images']['open'] ?? '', $row['images']['alt'] ?? $row['title']);
            $cat = ($row['category'] ?? '') === 'توری پنجره' ? $catLace : $catWindow;
            $slug = Str::slug($row['title'], '-', 'fa');
            if ($slug === '') {
                $slug = 'product-'.($row['images']['folder_number'] ?? ($i + 1));
            }
            SafeSeed::missing(Product::class, ['slug' => $slug], [
                'category_id' => $cat->id,
                'name' => $row['title'],
                'product_type' => $row['product_type'] ?? null,
                'image_close_id' => $close?->id,
                'image_open_id' => $open?->id,
                'alt_text' => $row['images']['alt'] ?? $row['title'],
                'folder_number' => $row['images']['folder_number'] ?? ($i + 1),
                'sort_order' => $i + 1,
                'show_on_home' => $i < 4,
                'status' => 'published',
                'seo_title' => $row['title'],
                'seo_description' => $row['title'],
            ]);
        }

        $projectsPath = $root.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'projects-data.json';
        if (! is_file($projectsPath)) {
            $projectsPath = $root.DIRECTORY_SEPARATOR.'projects-data.json';
        }
        $pdata = is_file($projectsPath) ? json_decode((string) file_get_contents($projectsPath), true) : [];
        foreach ($pdata['projects'] ?? [] as $row) {
            $img = $this->mediaFromSite($row['image'] ?? '', $row['imageAlt'] ?? $row['title']);
            $slug = $row['slug'] ?? Str::slug($row['title'], '-', 'fa');
            if ($slug === '') {
                $slug = 'project-'.($row['id'] ?? $row['order'] ?? uniqid());
            }
            SafeSeed::missing(Project::class, ['slug' => $slug], [
                'title' => $row['title'],
                'type' => $row['category'] ?? 'upvc',
                'type_label' => $row['categoryLabel'] ?? 'پنجره UPVC',
                'image_id' => $img?->id,
                'alt_text' => $row['imageAlt'] ?? $row['title'],
                'client_name' => $row['client_name'] ?? null,
                'location' => $row['location'] ?? null,
                'sort_order' => $row['order'] ?? 0,
                'show_on_home' => true,
                'status' => 'published',
                'seo_title' => $row['title'],
                'seo_description' => $row['imageAlt'] ?? $row['title'],
            ]);
        }
    }

    private function seedPartners(): void
    {
        $names = [
            1 => 'شرکت تحقیق، طراحی و تولید موتور ایران خودرو (IPCO)',
            2 => 'Volvo — پارسیان پیشرو صنعت',
            3 => 'شرکت پتروپالایش نگین مکران',
            4 => 'سازمان بازنشستگی شهرداری تهران',
            5 => 'آرین رشد افزا (آرا)',
            6 => 'تحکیم سازه شهریار',
        ];
        foreach ($names as $n => $name) {
            $media = $this->mediaFromSite('assets/Images/Company Logo/'.$n.'.webp', $name);
            SafeSeed::missing(Partner::class, ['name' => $name], [
                'alt_text' => $name,
                'image_id' => $media?->id,
                'sort_order' => $n,
                'is_active' => true,
            ]);
        }
        $this->mediaFromSite('assets/Video/intro.mp4', 'ویدیو معرفی آلوین');
    }

    private function mediaFromSite(string $relative, string $alt): ?Media
    {
        $relative = str_replace('\\', '/', $relative);
        if ($relative === '') {
            return null;
        }
        $abs = base_path(str_replace('/', DIRECTORY_SEPARATOR, $relative));
        $size = is_file($abs) ? filesize($abs) : 0;
        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        $kind = $ext === 'mp4' || $ext === 'webm' ? 'video' : 'image';
        $mime = match ($ext) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'mp4' => 'video/mp4',
            default => 'application/octet-stream',
        };

        return SafeSeed::missing(Media::class, ['disk' => 'site', 'path' => ltrim($relative, '/')], [
            'filename' => basename($relative),
            'original_name' => basename($relative),
            'mime' => $mime,
            'size' => $size ?: 0,
            'kind' => $kind,
            'alt' => $alt,
        ]);
    }
}
