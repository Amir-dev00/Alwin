<?php

namespace App\Support;

final class SiteCopy
{
    /**
     * Calculator chrome only — not pricing math.
     *
     * @return array<string, array{label: string, type: string, value: string}>
     */
    public static function calculator(): array
    {
        return [
            'calc_title' => ['label' => 'عنوان پنجره', 'type' => 'text', 'value' => 'محاسبه آنلاین قیمت'],
            'calc_close' => ['label' => 'برچسب بستن', 'type' => 'text', 'value' => 'بستن'],
            'calc_step_1' => ['label' => 'مرحله ۱', 'type' => 'text', 'value' => 'جنس'],
            'calc_step_2' => ['label' => 'مرحله ۲', 'type' => 'text', 'value' => 'دسته'],
            'calc_step_3' => ['label' => 'مرحله ۳', 'type' => 'text', 'value' => 'مدل'],
            'calc_step_4' => ['label' => 'مرحله ۴', 'type' => 'text', 'value' => 'جزئیات'],
            'calc_step_5' => ['label' => 'مرحله ۵', 'type' => 'text', 'value' => 'تماس'],
            'calc_lead_1' => ['label' => 'راهنمای مرحله جنس', 'type' => 'text', 'value' => 'جنس پروفیل را انتخاب کنید'],
            'calc_lead_2' => ['label' => 'راهنمای مرحله دسته', 'type' => 'text', 'value' => 'دسته محصول را انتخاب کنید'],
            'calc_lead_3' => ['label' => 'راهنمای مرحله مدل', 'type' => 'text', 'value' => 'مدل دقیق را انتخاب کنید'],
            'calc_lead_5' => ['label' => 'راهنمای مرحله تماس', 'type' => 'textarea', 'value' => 'برای دریافت مشاوره رایگان، اطلاعات تماس را وارد کنید'],
            'calc_search_placeholder' => ['label' => 'جستجوی مدل', 'type' => 'text', 'value' => 'جستجو در مدل‌ها...'],
            'calc_label_profile' => ['label' => 'برچسب پروفیل', 'type' => 'text', 'value' => 'پروفیل'],
            'calc_label_glass' => ['label' => 'برچسب شیشه', 'type' => 'text', 'value' => 'شیشه'],
            'calc_label_hardware' => ['label' => 'برچسب یراق', 'type' => 'text', 'value' => 'یراق'],
            'calc_label_hardware_type' => ['label' => 'برچسب نوع بازشو', 'type' => 'text', 'value' => 'نوع بازشو'],
            'calc_label_width' => ['label' => 'برچسب عرض', 'type' => 'text', 'value' => 'عرض (cm)'],
            'calc_label_height' => ['label' => 'برچسب ارتفاع', 'type' => 'text', 'value' => 'ارتفاع (cm)'],
            'calc_width_placeholder' => ['label' => 'نمونه عرض', 'type' => 'text', 'value' => '۱۲۰'],
            'calc_height_placeholder' => ['label' => 'نمونه ارتفاع', 'type' => 'text', 'value' => '۱۵۰'],
            'calc_estimate_label' => ['label' => 'برچسب برآورد', 'type' => 'text', 'value' => 'برآورد قیمت'],
            'calc_estimate_hint' => ['label' => 'توضیح برآورد', 'type' => 'text', 'value' => 'تقریبی — تأیید نهایی پس از بازدید'],
            'calc_quantity_label' => ['label' => 'برچسب تعداد', 'type' => 'text', 'value' => 'تعداد'],
            'calc_name_label' => ['label' => 'برچسب نام', 'type' => 'text', 'value' => 'نام'],
            'calc_name_placeholder' => ['label' => 'نمونه نام', 'type' => 'text', 'value' => 'نام شما'],
            'calc_phone_label' => ['label' => 'برچسب تلفن', 'type' => 'text', 'value' => 'شماره تماس'],
            'calc_phone_placeholder' => ['label' => 'نمونه تلفن', 'type' => 'text', 'value' => '09xxxxxxxxx'],
            'calc_success_title' => ['label' => 'عنوان موفقیت', 'type' => 'text', 'value' => 'درخواست شما ثبت شد'],
            'calc_success_text' => ['label' => 'متن موفقیت', 'type' => 'textarea', 'value' => 'کارشناسان ALWIN به زودی با شما تماس می‌گیرند.'],
            'calc_btn_close' => ['label' => 'دکمه بستن موفقیت', 'type' => 'text', 'value' => 'بستن'],
            'calc_btn_back' => ['label' => 'دکمه قبلی', 'type' => 'text', 'value' => 'قبلی'],
            'calc_btn_next' => ['label' => 'دکمه ادامه', 'type' => 'text', 'value' => 'ادامه'],
            'calc_btn_submit' => ['label' => 'دکمه ثبت', 'type' => 'text', 'value' => 'ثبت درخواست'],
            'calc_btn_submitting' => ['label' => 'متن در حال ثبت', 'type' => 'text', 'value' => 'در حال ثبت...'],
            'calc_all_filters' => ['label' => 'فیلتر همه', 'type' => 'text', 'value' => 'همه'],
            'calc_model_count' => ['label' => 'شمارش مدل ({n} جایگزین می‌شود)', 'type' => 'text', 'value' => '{n} مدل'],
            'calc_empty_models' => ['label' => 'مدل پیدا نشد', 'type' => 'text', 'value' => 'مدلی با این نام پیدا نشد.'],
            'calc_selected_model' => ['label' => 'برچسب مدل انتخاب‌شده', 'type' => 'text', 'value' => 'مدل انتخاب‌شده'],
            'calc_summary_model' => ['label' => 'خلاصه: مدل', 'type' => 'text', 'value' => 'مدل'],
            'calc_summary_profile_glass' => ['label' => 'خلاصه: پروفیل/شیشه', 'type' => 'text', 'value' => 'پروفیل / شیشه'],
            'calc_summary_profile_only' => ['label' => 'خلاصه: جنس/پروفیل', 'type' => 'text', 'value' => 'جنس / پروفیل'],
            'calc_summary_size' => ['label' => 'خلاصه: ابعاد', 'type' => 'text', 'value' => 'ابعاد'],
            'calc_summary_cm' => ['label' => 'واحد ابعاد', 'type' => 'text', 'value' => 'سانتی‌متر'],
            'calc_summary_estimate' => ['label' => 'خلاصه: برآورد', 'type' => 'text', 'value' => 'برآورد'],
            'calc_currency' => ['label' => 'واحد پول', 'type' => 'text', 'value' => 'تومان'],
            'calc_breakdown_profile' => ['label' => 'جزء پروفیل', 'type' => 'text', 'value' => 'پروفیل'],
            'calc_breakdown_glass' => ['label' => 'جزء شیشه', 'type' => 'text', 'value' => 'شیشه'],
            'calc_breakdown_hardware' => ['label' => 'جزء یراق', 'type' => 'text', 'value' => 'یراق'],
            'calc_breakdown_screen' => ['label' => 'جزء توری', 'type' => 'text', 'value' => 'توری'],
            'calc_error_size' => ['label' => 'خطای ابعاد', 'type' => 'text', 'value' => 'لطفاً عرض و ارتفاع را وارد کنید.'],
            'calc_error_contact' => ['label' => 'خطای تماس', 'type' => 'text', 'value' => 'لطفاً نام و شماره تماس را وارد کنید.'],
            'calc_error_submit' => ['label' => 'خطای ثبت', 'type' => 'text', 'value' => 'خطا در ثبت. لطفاً دوباره تلاش کنید.'],
        ];
    }

    /**
     * Extra site-wide strings (footer chrome, extra phones, SEO assets).
     *
     * @return array<int, array{group: string, key: string, label: string, type: string, value: string}>
     */
    public static function extraSettings(): array
    {
        return [
            ['group' => 'brand', 'key' => 'logo', 'label' => 'لوگوی سایت', 'type' => 'image', 'value' => 'assets/img/brand/logo.png'],
            ['group' => 'brand', 'key' => 'favicon', 'label' => 'فاوآیکون', 'type' => 'image', 'value' => 'assets/imgs/logo/favicon.png'],
            ['group' => 'seo', 'key' => 'seo_description_default', 'label' => 'توضیح پیش‌فرض گوگل', 'type' => 'textarea', 'value' => 'ALWIN — تولید و نصب پنجره دوجداره UPVC'],
            ['group' => 'seo', 'key' => 'og_image', 'label' => 'تصویر اشتراک‌گذاری', 'type' => 'image', 'value' => 'assets/img/brand/logo.png'],
            ['group' => 'contact', 'key' => 'website_display', 'label' => 'نمایش وب‌سایت', 'type' => 'text', 'value' => 'www.alwinco.ir'],
            ['group' => 'contact', 'key' => 'office_phones', 'label' => 'تلفن‌های دفتر (هر خط یک شماره)', 'type' => 'textarea', 'value' => "021-44245247\n021-44245429\n021-44252799"],
            ['group' => 'contact', 'key' => 'factory_title', 'label' => 'عنوان کارخانه', 'type' => 'text', 'value' => 'صفادشت — بلوار قبچاق'],
            ['group' => 'contact', 'key' => 'factory_address', 'label' => 'آدرس کارخانه', 'type' => 'textarea', 'value' => 'جاده ملارد - صفادشت، بلوار قبچاق، نبش شهدای پنجم، پلاک ۱'],
            ['group' => 'contact', 'key' => 'factory_phones', 'label' => 'تلفن‌های کارخانه (هر خط یک شماره)', 'type' => 'textarea', 'value' => "021-65583141\n021-65583140"],
            ['group' => 'footer', 'key' => 'footer_links_title', 'label' => 'عنوان لینک‌های فوتر', 'type' => 'text', 'value' => 'دسترسی سریع'],
            ['group' => 'footer', 'key' => 'footer_social_title', 'label' => 'عنوان شبکه‌های فوتر', 'type' => 'text', 'value' => 'شبکه‌های اجتماعی'],
            ['group' => 'footer', 'key' => 'sidebar_contact_title', 'label' => 'عنوان تماس منوی موبایل', 'type' => 'text', 'value' => 'تماس با ما'],
            ['group' => 'footer', 'key' => 'header_menu_open', 'label' => 'برچسب باز کردن منو', 'type' => 'text', 'value' => 'باز کردن منو'],
            ['group' => 'footer', 'key' => 'header_menu_close', 'label' => 'برچسب بستن منو', 'type' => 'text', 'value' => 'بستن منو'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function resolvedCalculator(): array
    {
        $settings = Site::settings();
        $out = [];
        foreach (self::calculator() as $key => $meta) {
            $out[$key] = $settings[$key] ?? $meta['value'];
        }

        return $out;
    }
}
