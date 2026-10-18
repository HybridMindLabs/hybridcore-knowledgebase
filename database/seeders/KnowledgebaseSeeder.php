<?php

namespace Hybridcore\Knowledgebase\Database\Seeders;

use Hybridcore\Knowledgebase\Models\KbArticle;
use Hybridcore\Knowledgebase\Models\KbCategory;
use Illuminate\Database\Seeder;

class KnowledgebaseSeeder extends Seeder
{
    public function run(): void
    {
        // Category 1: Game Servers & Rules
        $catRules = KbCategory::firstOrCreate(
            ['slug' => 'server-rules'],
            [
                'name' => 'Сървъри и Правила',
                'description' => 'Официални правила за игра, общи изисквания и правила за поведение в сървърите.',
                'icon' => 'Server',
                'color' => 'blue',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        // Category 2: Reports & Appeals
        $catReports = KbCategory::firstOrCreate(
            ['slug' => 'reports-and-appeals'],
            [
                'name' => 'Оплаквания и Репорти',
                'description' => 'Инструкции и шаблони за докладване на некоректни играчи, чийтъри или злоупотреби от админи.',
                'icon' => 'ShieldAlert',
                'color' => 'red',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        // Category 3: Modifications & Tweaks
        $catMods = KbCategory::firstOrCreate(
            ['slug' => 'modifications'],
            [
                'name' => 'Модификации и Настройки',
                'description' => 'Ръководства за инсталиране на карти, скинове, настройки на рейтове (rates) и повишаване на FPS.',
                'icon' => 'Wrench',
                'color' => 'amber',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        // Category 4: General FAQ
        $catFaq = KbCategory::firstOrCreate(
            ['slug' => 'faq-general'],
            [
                'name' => 'Общи въпроси и Помощ',
                'description' => 'Често задавани въпроси, контакти с екипа и връзка с Discord / Forum общността.',
                'icon' => 'HelpCircle',
                'color' => 'emerald',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        // Article 1: How to report player
        KbArticle::firstOrCreate(
            ['slug' => 'how-to-report-a-player-or-cheater'],
            [
                'category_id' => $catReports->id,
                'title' => 'Как правилно да докладвате играч или чийтър',
                'excerpt' => 'Стъпка по стъпка как да запишете доказателство (демо, скрийншот, SteamID) и къде да пуснете валидно оплакване.',
                'content' => "## Как да докладвате нарушител\n\nЗа да може нашият екип от администратори бързо и справедливо да вземе мерки, всяко оплакване трябва да съдържа необходимите доказателства.\n\n### Задължителна информация при докладване:\n1. **SteamID или Игрови идентификатор** на нарушителя (напишете `status` в конзолата, за да го копирате).\n2. **Име на сървъра** и приблизителен **час на нарушението**.\n3. **Кратко описание** на случилото се (напр. *Aimbot*, *Wallhack*, *Обиди към играчи*, *Грифинг*).\n4. **Доказателство**: Демо запис (`record demoname` в конзолата) или видеоклип качено в YouTube/Streamable.\n\n> [!WARNING] Оплаквания без посочено SteamID или без видео/демо доказателство не могат да бъдат разглеждани от администраторите!\n\n### Къде да изпратите оплакването?\nПосетете секция **Оплаквания** във форума или отворете тикет в нашия официален Discord сървър.",
                'status' => 'published',
                'is_pinned' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'views_count' => 142,
                'helpful_count' => 38,
                'unhelpful_count' => 1,
                'meta_title' => 'Как правилно да докладвате играч или чийтър — База знания',
                'meta_description' => 'Пълно ръководство как да запишете доказателство и демо, за да докладвате играч или чийтър.',
                'published_at' => now(),
            ]
        );

        // Article 2: How to report admin abuse
        KbArticle::firstOrCreate(
            ['slug' => 'how-to-report-admin-abuse'],
            [
                'category_id' => $catReports->id,
                'title' => 'Подаване на сигнал за злоупотреба с админ права',
                'excerpt' => 'Процедура за разглеждане на неправомерни банове, слейове или злоупотреби от страна на членове на екипа.',
                'content' => "## Сигнали за злоупотреба от администратори\n\nНие държим на коректното и безпристрастно отношение от страна на нашия екип. Ако смятате, че администратор е злоупотребил с правата си или ви е наложил несправедливо наказание:\n\n### Стъпки:\n1. Запишете **точната дата и час**, както и **името на администратора**.\n2. Приложете конзолен лог или демо от играта.\n3. Подайте сигнал директно към Главните администратори (Head Admins) през тикет системата.\n\n> [!NOTE] Всички сигнали се разглеждат конфиденциално от висшето ръководство, а логовете от сървъра се проверяват обстойно.",
                'status' => 'published',
                'is_pinned' => false,
                'is_featured' => true,
                'sort_order' => 2,
                'views_count' => 89,
                'helpful_count' => 24,
                'unhelpful_count' => 0,
                'meta_title' => 'Подаване на сигнал за злоупотреба с админ права',
                'meta_description' => 'Как да подадете сигнал при несправедлив бан или нарушаване на правилата от администратор.',
                'published_at' => now(),
            ]
        );

        // Article 3: General rules
        KbArticle::firstOrCreate(
            ['slug' => 'official-game-server-rules'],
            [
                'category_id' => $catRules->id,
                'title' => 'Официални правила на гейм сървърите',
                'excerpt' => 'Основни норми и правила за поведение във всички наши гейм сървъри.',
                'content' => "## Основни правила на сървърите\n\n1. **Забранено е използването на всякакъв софтуер, даващ предимство (чийтове, скриптове, хакове)** — Наказание: Перманентен бан.\n2. **Уважавайте останалите играчи** — обиди на расова, етническа или религиозна основа се наказват с mute/gag или временен бан.\n3. **Забранена е рекламата** на други сайтове, сървъри или услуги.\n4. **Спазвайте указанията на администраторите** по време на игра.\n\nПриятна игра в нашите сървъри!",
                'status' => 'published',
                'is_pinned' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'views_count' => 310,
                'helpful_count' => 95,
                'unhelpful_count' => 3,
                'meta_title' => 'Официални правила на гейм сървърите',
                'meta_description' => 'Прочетете правилата за игра и поведение в нашите гейм сървъри.',
                'published_at' => now(),
            ]
        );

        // Article 4: FPS & Rates Optimization
        KbArticle::firstOrCreate(
            ['slug' => 'fps-and-network-optimization-guide'],
            [
                'category_id' => $catMods->id,
                'title' => 'Оптимизация на FPS, Ping и мрежови настройки',
                'excerpt' => 'Препоръчителни конзолни команди и настройки за максимално плавна игра без загуба на пакети (choke/loss).',
                'content' => "## Оптимизация на играта и връзката със сървъра\n\nЗа да имате най-доброто изживяване и нулев лаг, използвайте следните препоръчителни настройки в конзолата:\n\n```bash\nrate 786432\ncl_updaterate 128\ncl_cmdrate 128\ncl_interp 0\ncl_interp_ratio 1\nfps_max 0\n```\n\nТези команди осигуряват максимална честота на опресняване между вашия клиент и сървъра.",
                'status' => 'published',
                'is_pinned' => false,
                'is_featured' => false,
                'sort_order' => 1,
                'views_count' => 205,
                'helpful_count' => 61,
                'unhelpful_count' => 2,
                'meta_title' => 'Оптимизация на FPS, Ping и мрежови настройки',
                'meta_description' => 'Ръководство за конзолни команди и мрежови настройки за оптимална игра.',
                'published_at' => now(),
            ]
        );
    }
}
