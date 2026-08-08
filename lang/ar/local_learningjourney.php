<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Arabic language strings for the Learning Journey plugin.
 *
 * @package    local_learningjourney
 * @copyright  2026 Learning Journey contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'رحلة التعلّم';
$string['resulttitle'] = 'نتيجتك';
$string['coursesettings'] = 'رحلة التعلّم';
$string['coursesettings_intro'] = 'تنطبق هذه الإعدادات على {$a} فقط. وكل إعداد يُترك على الوضع الافتراضي للموقع يتبع الإعداد العام.';
$string['usesitedefault'] = 'استخدام الإعداد الافتراضي للموقع';

// Capabilities.
$string['learningjourney:manage'] = 'ضبط رحلة التعلّم لمقرر دراسي';
$string['learningjourney:viewothers'] = 'عرض صفحة نتيجة رحلة التعلّم الخاصة بمتعلّم آخر';

// Administration pages.
$string['settingspage_general'] = 'عام';
$string['settingspage_messages'] = 'الرسائل';
$string['settingspage_appearance'] = 'المظهر';
$string['settingspage_effects'] = 'المؤثرات';
$string['settingspage_display'] = 'العرض والتقييم';

// Select options.
$string['layout_standard'] = 'تخطيط الصفحة القياسي';
$string['layout_focused'] = 'تخطيط مركّز بدون التنقل الثانوي';
$string['unitmode_section'] = 'قسم المقرر';
$string['unitmode_activity'] = 'نشاط متتبَّع الإنجاز';
$string['unitmode_lesson'] = 'نشاط الدرس';
$string['iconset_emoji'] = 'رموز تعبيرية';
$string['iconset_fontawesome'] = 'أيقونات السمة';
$string['iconset_none'] = 'بدون أيقونات';

// General settings.
$string['setting_enabled'] = 'تفعيل رحلة التعلّم';
$string['setting_enabled_desc'] = 'عند التفعيل، يرى المتعلّم صفحة رحلة التعلّم مباشرة بعد إرسال محاولة الاختبار.';
$string['setting_layout'] = 'تخطيط الصفحة';
$string['setting_layout_desc'] = 'اختر ما إذا كانت صفحة النتيجة تحتفظ بتنقل المقرر القياسي أو تستخدم تخطيطًا مركّزًا.';
$string['setting_autoredirect'] = 'إعادة التوجيه التلقائي';
$string['setting_autoredirect_desc'] = 'نقل المتعلّم تلقائيًا بعد عدّ تنازلي ظاهر يمكن إلغاؤه. معطّل افتراضيًا.';
$string['setting_redirectdelay'] = 'مهلة إعادة التوجيه';
$string['setting_redirectdelay_desc'] = 'مدة العدّ التنازلي قبل نقل المتعلّم. أقصر مهلة مسموح بها عشر ثوانٍ.';
$string['setting_unitmode'] = 'ما الذي يُحتسب درسًا';
$string['setting_unitmode_desc'] = 'اختر طريقة احتساب التقدّم مثل «الدرس 1 من 5».';
$string['setting_usefallbackgradepass'] = 'استخدام درجة نجاح احتياطية';
$string['setting_usefallbackgradepass_desc'] = 'تطبيق درجة نجاح عامة على الاختبارات التي لا تحتوي على درجة نجاح في دفتر الدرجات.';
$string['setting_fallbackgradepass'] = 'درجة النجاح الاحتياطية';
$string['setting_fallbackgradepass_desc'] = 'النسبة المئوية المطبَّقة عندما لا يكون للاختبار درجة نجاح خاصة به.';

// Message settings.
$string['setting_successtitle'] = 'عنوان النجاح';
$string['setting_successtitle_desc'] = 'العنوان الذي يظهر للمتعلّم الذي بلغ درجة النجاح. اتركه فارغًا لاستخدام النص المترجم الافتراضي.';
$string['setting_successmessage'] = 'رسالة النجاح';
$string['setting_successmessage_desc'] = 'الرسالة التي تظهر للمتعلّم الذي بلغ درجة النجاح. اتركها فارغة لاستخدام النص المترجم الافتراضي.';
$string['setting_failtitle'] = 'عنوان التشجيع';
$string['setting_failtitle_desc'] = 'العنوان الذي يظهر للمتعلّم الذي لم يبلغ درجة النجاح بعد. اتركه فارغًا لاستخدام النص المترجم الافتراضي.';
$string['setting_failmessage'] = 'رسالة التشجيع';
$string['setting_failmessage_desc'] = 'الرسالة التي تظهر للمتعلّم الذي لم يبلغ درجة النجاح بعد. اتركها فارغة لاستخدام النص المترجم الافتراضي.';
$string['setting_islamicsuccess'] = 'رسالة نجاح إضافية';
$string['setting_islamicsuccess_desc'] = 'سطر إضافي اختياري يظهر أسفل رسالة النجاح.';
$string['setting_islamicencouragement'] = 'رسالة تشجيع إضافية';
$string['setting_islamicencouragement_desc'] = 'سطر إضافي اختياري يظهر أسفل رسالة التشجيع.';
$string['setting_coursecompletemessage'] = 'رسالة إتمام المقرر';
$string['setting_coursecompletemessage_desc'] = 'الرسالة التي تظهر عند عدم بقاء أي نشاط في المقرر.';
$string['setting_studyadvice'] = 'إرشاد للمراجعة';
$string['setting_studyadvice_desc'] = 'إرشاد اختياري يُعرض بعد محاولة غير موفّقة.';
$string['setting_continuelabel'] = 'نص زر المتابعة';
$string['setting_continuelabel_desc'] = 'يستبدل نص زر المتابعة الرئيسي. اتركه فارغًا لعرض اسم النشاط التالي.';
$string['setting_retrylabel'] = 'نص زر إعادة المحاولة';
$string['setting_retrylabel_desc'] = 'يستبدل نص زر إعادة المحاولة.';

// Appearance settings.
$string['setting_themecolour'] = 'لون السمة';
$string['setting_themecolour_desc'] = 'اللون الأساسي المستخدم للعناوين والإبرازات.';
$string['setting_buttoncolour'] = 'لون الزر';
$string['setting_buttoncolour_desc'] = 'لون خلفية الزر الرئيسي.';
$string['setting_buttontextcolour'] = 'لون نص الزر';
$string['setting_buttontextcolour_desc'] = 'لون نص الزر الرئيسي. اختر قيمة يتباين لونها جيدًا مع لون الزر.';
$string['setting_progressbarcolour'] = 'لون شريط التقدّم';
$string['setting_progressbarcolour_desc'] = 'لون تعبئة شريط التقدّم.';
$string['setting_progressbgcolour'] = 'لون مسار شريط التقدّم';
$string['setting_progressbgcolour_desc'] = 'لون الجزء غير المعبّأ من شريط التقدّم.';
$string['setting_backgroundcolour'] = 'لون الخلفية';
$string['setting_backgroundcolour_desc'] = 'لون خلفية لوحة النتيجة.';
$string['setting_backgroundimage'] = 'صورة الخلفية';
$string['setting_backgroundimage_desc'] = 'صورة خلفية زخرفية اختيارية للوحة النتيجة.';
$string['setting_iconset'] = 'نمط الأيقونات';
$string['setting_iconset_desc'] = 'اختر نمط الأيقونات المستخدم في صفحة النتيجة.';

// Effect settings.
$string['setting_effectconfetti'] = 'قصاصات الاحتفال';
$string['setting_effectconfetti_desc'] = 'عرض حركة قصيرة لقصاصات الاحتفال بعد محاولة ناجحة.';
$string['setting_effectstars'] = 'حركة النجوم';
$string['setting_effectstars_desc'] = 'تحريك تقييم النجوم عند ظهوره.';
$string['setting_effecttrophy'] = 'الكأس';
$string['setting_effecttrophy_desc'] = 'عرض كأس بجانب رسالة النجاح.';
$string['setting_effectfireworks'] = 'الألعاب النارية';
$string['setting_effectfireworks_desc'] = 'عرض حركة قصيرة للألعاب النارية بعد محاولة ناجحة.';
$string['setting_effectbadge'] = 'شارة الإنجاز';
$string['setting_effectbadge_desc'] = 'عرض شارة إنجاز زخرفية عند عدم منح أي شارة من شارات مودل.';
$string['setting_effectsound'] = 'صوت التصفيق';
$string['setting_effectsound_desc'] = 'إتاحة زر تشغيل لصوت التصفيق. لا يُشغَّل الصوت تلقائيًا أبدًا.';
$string['setting_soundfile'] = 'ملف صوت التصفيق';
$string['setting_soundfile_desc'] = 'ارفع ملف الصوت المراد إتاحته. لا يأتي أي ملف صوتي مع الإضافة.';

// Display and scoring settings.
$string['setting_showscore'] = 'عرض الدرجة النهائية';
$string['setting_showscore_desc'] = 'عرض الدرجة المحققة في المحاولة.';
$string['setting_showpercentage'] = 'عرض النسبة المئوية';
$string['setting_showpercentage_desc'] = 'عرض النسبة المئوية المحققة في المحاولة.';
$string['setting_showgradepass'] = 'عرض درجة النجاح';
$string['setting_showgradepass_desc'] = 'عرض درجة النجاح التي تم تطبيقها.';
$string['setting_showstatus'] = 'عرض حالة النجاح أو الإخفاق';
$string['setting_showstatus_desc'] = 'عرض نتيجة المحاولة في بطاقة حالة.';
$string['setting_showtimetaken'] = 'عرض الوقت المستغرق';
$string['setting_showtimetaken_desc'] = 'عرض المدة التي قضاها المتعلّم في المحاولة.';
$string['setting_showattempt'] = 'عرض رقم المحاولة';
$string['setting_showattempt_desc'] = 'عرض رقم هذه المحاولة وعدد المحاولات المتبقية.';
$string['setting_showstars'] = 'عرض تقييم النجوم';
$string['setting_showstars_desc'] = 'عرض تقييم النجوم الذي حصل عليه المتعلّم.';
$string['setting_showprogress'] = 'عرض شريط التقدّم';
$string['setting_showprogress_desc'] = 'عرض شريط تقدّم المقرر. إيقافه يتخطى حسابه أيضًا.';
$string['setting_showcoursecompletion'] = 'عرض إتمام المقرر';
$string['setting_showcoursecompletion_desc'] = 'عرض النسبة المئوية الإجمالية لإتمام المقرر.';
$string['setting_showbadges'] = 'عرض الشارات';
$string['setting_showbadges_desc'] = 'عرض الشارات التي حصل عليها المتعلّم في هذا المقرر.';
$string['setting_showreviewlink'] = 'عرض رابط المراجعة';
$string['setting_showreviewlink_desc'] = 'إتاحة رابط لصفحة مراجعة الاختبار القياسية في مودل عند السماح بالمراجعة.';
$string['setting_starthresholds'] = 'عتبات النجوم';
$string['setting_starthresholds_desc'] = 'خمس نسب مئوية تصاعدية مفصولة بفواصل، تُمنح عندها كل نجمة إضافية.';
$string['setting_manualbadgeid'] = 'الشارة اليدوية الممنوحة';
$string['setting_manualbadgeid_desc'] = 'معرّف شارة يدوية تُمنح عند نجاح المحاولة. اتركه صفرًا ليتولى نظام شارات مودل جميع عمليات المنح.';

// Default messages, used whenever the matching setting is left empty.
$string['default_successtitle'] = 'تهانينا!';
$string['default_successmessage'] = 'لقد اجتزت هذا الاختبار. واصل تقدّمك.';
$string['default_failtitle'] = 'واصل المحاولة';
$string['default_failmessage'] = 'كل محاولة تساعدك على التحسّن. راجع الدرس ثم حاول مرة أخرى.';
$string['default_islamicsuccess'] = 'زادك الله علمًا نافعًا.';
$string['default_islamicencouragement'] = 'زادك الله علمًا. والنجاح يأتي بالمثابرة.';
$string['default_coursecomplete'] = 'لقد أتممت هذا المقرر بنجاح.';
$string['default_studyadvice'] = 'قد يفيدك مراجعة الدرس السابق قبل إعادة محاولة الاختبار.';
$string['default_continuelabel'] = 'واصل التعلّم';

// Verdict independent defaults added for the pending state.
$string['default_pendingtitle'] = 'تم استلام إجاباتك';
$string['default_pendingmessage'] = 'تم إرسال إجاباتك وهي قيد المراجعة. وستظهر نتيجتك بعد اكتمال التصحيح.';

// Progress sentences.
$string['progress_line'] = 'لقد أكملت {$a->completed} من {$a->total} دروس. وبقي {$a->remaining} فقط. واصل عملك الممتاز.';
$string['progress_allcomplete'] = 'لقد أكملت جميع دروس هذا المقرر.';
$string['unitlabel_section'] = 'الدرس {$a->index} من {$a->total}';
$string['unitlabel_activity'] = 'النشاط {$a->index} من {$a->total}';
$string['unitlabel_lesson'] = 'الدرس {$a->index} من {$a->total}';
$string['unitlabel_completed'] = 'تم إكمال {$a->completed} من {$a->total} دروس';

// Additional on page labels.
$string['label_continueto'] = 'المتابعة إلى {$a}';
$string['label_achievement'] = 'إنجاز جديد';
$string['label_nomark'] = 'لا توجد درجة نجاح محددة';
$string['advice_reviewnamed'] = 'قد يفيدك مراجعة {$a} قبل إعادة محاولة الاختبار.';
$string['countdown_remaining'] = 'المتابعة خلال {$a} ثانية';

// On page labels.
$string['label_finalscore'] = 'الدرجة النهائية';
$string['label_percentage'] = 'النسبة المئوية';
$string['label_passinggrade'] = 'درجة النجاح';
$string['label_status'] = 'الحالة';
$string['label_timetaken'] = 'الوقت المستغرق';
$string['label_attempt'] = 'المحاولة';
$string['label_attemptsremaining'] = 'المحاولات المتبقية';
$string['label_overallgrade'] = 'درجة الاختبار الإجمالية';
$string['label_passed'] = 'ناجح';
$string['label_failed'] = 'لم تنجح بعد';
$string['label_pending'] = 'في انتظار التصحيح';
$string['label_tryagain'] = 'حاول مرة أخرى';
$string['label_reviewquiz'] = 'مراجعة الاختبار';
$string['label_reviewlesson'] = 'مراجعة الدرس';
$string['label_returntocourse'] = 'العودة إلى المقرر';
$string['label_continuestudying'] = 'واصل الدراسة';
$string['label_stayonpage'] = 'البقاء في هذه الصفحة';
$string['label_playsound'] = 'تشغيل الصوت';
$string['label_fallbackapplied'] = 'تم تطبيق درجة النجاح الافتراضية للموقع على هذا الاختبار.';

// Accessible descriptions.
$string['aria_progressbar'] = 'إتمام المقرر';
$string['aria_stars'] = '{$a->earned} من {$a->total} نجوم';
$string['aria_countdown'] = 'الوقت المتبقي قبل نقلك إلى النشاط التالي';

// Administration page introductions.
$string['settingspage_general_intro'] = 'يتحكم في وقت ظهور صفحة رحلة التعلّم وكيفية تحديد درجة النجاح. تعمل الإضافة بدون أي ضبط؛ ولكل ما يلي قيمة افتراضية مناسبة.';
$string['settingspage_messages_intro'] = 'كل رسالة تُترك فارغة تعود إلى النص المترجم الافتراضي، لذا يكون الموقع صحيحًا بالعربية والإنجليزية دون تدخل. املأ الحقل فقط عندما تريد استبدال الصياغة الأصلية.';
$string['settingspage_appearance_intro'] = 'الألوان والخلفية ونمط الأيقونات لصفحة النتيجة. يجب أن تكون قيم الألوان ست عشرية، ويظهر تنبيه إذا كان التباين بين لونين أقل من النسبة الموصى بها.';
$string['settingspage_effects_intro'] = 'احتفالات اختيارية تُعرض بعد المحاولة الناجحة. تُوقف جميع المؤثرات تلقائيًا لمن طلب تقليل الحركة، ولا يُشغَّل الصوت أبدًا دون نقرة متعمدة.';
$string['settingspage_display_intro'] = 'اختر ما يراه المتعلّم في صفحة النتيجة، وحدد العتبات التي تُمنح عندها كل نجمة إضافية.';

// Additional interface labels.
$string['coursesettings_help'] = 'كل إعداد أدناه مقترن بمربع «استخدام الإعداد الافتراضي للموقع». أزل علامة المربع لتغيير الإعداد لهذا المقرر فقط، وأعد وضعها لإزالة التخصيص تمامًا.';
$string['label_trophy'] = 'كأس ممنوح';
$string['label_percentcomplete'] = 'اكتمل {$a}%';

// Validation messages.
$string['error_notinteger'] = 'أدخل عددًا صحيحًا.';
$string['error_outofrange'] = 'أدخل عددًا صحيحًا بين {$a->min} و {$a->max}.';
$string['error_redirectdelay'] = 'يجب ألا يقل العدّ التنازلي عن {$a} ثوانٍ حتى يتمكن المتعلّم من قراءة نتيجته وإلغاء الانتقال.';
$string['error_attemptnotfound'] = 'تعذر العثور على محاولة الاختبار هذه. ربما تم حذفها أو أن الرابط غير صحيح.';

// Errors and warnings.
$string['error_invalidcolour'] = 'أدخل اللون بصيغة ست عشرية من ثلاث أو ست خانات، مثل ‎#1d6f42‎.';
$string['error_thresholdcount'] = 'أدخل {$a} عتبات بالضبط، مفصولة بفواصل.';
$string['error_thresholdnumeric'] = 'يجب أن تكون كل عتبة عددًا صحيحًا.';
$string['error_thresholdorder'] = 'يجب أن تكون العتبات تصاعدية وألا تتجاوز 100.';
$string['warning_lowcontrast'] = 'الألوان المختارة لـ {$a->name} تعطي نسبة تباين {$a->ratio} إلى 1، وهي أقل من النسبة الموصى بها 4.5 إلى 1.';

// Report.
$string['report_title'] = 'اختبارات بلا درجة نجاح';
$string['report_noquizzes'] = 'كل اختبار في هذا الموقع لديه درجة نجاح محددة.';
$string['report_course'] = 'المقرر';
$string['report_quiz'] = 'الاختبار';

// Mobile app.
$string['mobile_viewprogress'] = 'رحلة التعلّم';
$string['mobile_nodata'] = 'لا توجد معلومات عن رحلة التعلّم لعرضها بعد.';

// Privacy.
$string['privacy:metadata'] = 'لا تخزّن إضافة رحلة التعلّم أي بيانات شخصية في قاعدة البيانات. وإعدادات المقرر هي إعدادات ضبط وليست بيانات مستخدم.';
$string['privacy:preference:mute'] = 'ما إذا اختار المتعلّم كتم صوت الاحتفال الاختياري.';
$string['privacy:preference:mute:on'] = 'صوت الاحتفال مكتوم.';
$string['privacy:preference:mute:off'] = 'صوت الاحتفال غير مكتوم.';
