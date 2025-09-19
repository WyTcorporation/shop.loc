<?php

namespace Database\Support;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Support\Str;

class TranslationGenerator
{
    private const LOCALE_MAP = [
        'uk' => 'uk_UA',
        'en' => 'en_US',
        'ru' => 'ru_RU',
        'pt' => 'pt_PT',
    ];

    private const ADJECTIVES = [
        ['uk' => 'Преміум', 'en' => 'Premium', 'ru' => 'Премиум', 'pt' => 'Premium'],
        ['uk' => 'Міський', 'en' => 'Urban', 'ru' => 'Городской', 'pt' => 'Urbano'],
        ['uk' => 'Натуральний', 'en' => 'Natural', 'ru' => 'Натуральный', 'pt' => 'Natural'],
        ['uk' => 'Свіжий', 'en' => 'Fresh', 'ru' => 'Свежий', 'pt' => 'Fresco'],
        ['uk' => 'Збалансований', 'en' => 'Balanced', 'ru' => 'Сбалансированный', 'pt' => 'Equilibrado'],
        ['uk' => 'Лімітований', 'en' => 'Limited', 'ru' => 'Лимитированный', 'pt' => 'Limitado'],
    ];

    private const THEMES = [
        'coffee' => [
            'category' => [
                'uk' => 'Кава та чай',
                'en' => 'Coffee & Tea',
                'ru' => 'Кофе и чай',
                'pt' => 'Café e chá',
            ],
            'product_noun' => [
                'uk' => 'кавові зерна',
                'en' => 'coffee beans',
                'ru' => 'кофейные зёрна',
                'pt' => 'grãos de café',
            ],
            'product_description' => [
                'uk' => 'Свіжообсмажені зерна {brand} з нотами карамелі та фундука.',
                'en' => 'Freshly roasted {brand} beans with caramel and hazelnut notes.',
                'ru' => 'Свежообжаренные зерна {brand} с нотами карамели и фундука.',
                'pt' => 'Grãos {brand} recém-torrados com notas de caramelo e avelã.',
            ],
            'vendor_suffix' => [
                'uk' => 'Обсмажувальня',
                'en' => 'Roastery',
                'ru' => 'Обжарка',
                'pt' => 'Torrefação',
            ],
            'vendor_description' => [
                'uk' => '{brand} співпрацює з мікрофермами та обсмажує каву невеликими партіями.',
                'en' => '{brand} partners with micro farms and roasts coffee in small batches.',
                'ru' => '{brand} сотрудничает с микрофермами и обжаривает кофе малыми партиями.',
                'pt' => '{brand} colabora com microrroças e torra café em pequenos lotes.',
            ],
            'image_alt' => [
                'uk' => 'Пачка {product} №{index}',
                'en' => '{product} pack #{index}',
                'ru' => 'Упаковка {product} №{index}',
                'pt' => 'Pacote de {product} nº{index}',
            ],
        ],
        'tech' => [
            'category' => [
                'uk' => 'Гаджети та електроніка',
                'en' => 'Tech Gadgets',
                'ru' => 'Гаджеты и электроника',
                'pt' => 'Gadgets e tecnologia',
            ],
            'product_noun' => [
                'uk' => 'смарт-годинник',
                'en' => 'smartwatch',
                'ru' => 'смарт-часы',
                'pt' => 'relógio inteligente',
            ],
            'product_description' => [
                'uk' => 'Розумний гаджет {brand} з голосовим асистентом та NFC-оплатою.',
                'en' => '{brand} smart wearable with voice assistant and NFC payments.',
                'ru' => 'Умное устройство {brand} с голосовым ассистентом и NFC-оплатой.',
                'pt' => 'Dispositivo inteligente {brand} com assistente de voz e pagamentos NFC.',
            ],
            'vendor_suffix' => [
                'uk' => 'Лабораторія',
                'en' => 'Lab',
                'ru' => 'Лаборатория',
                'pt' => 'Laboratório',
            ],
            'vendor_description' => [
                'uk' => '{brand} створює міські гаджети зі скандинавським дизайном.',
                'en' => '{brand} crafts urban gadgets with Scandinavian-inspired design.',
                'ru' => '{brand} создаёт городские гаджеты со скандинавским дизайном.',
                'pt' => '{brand} cria gadgets urbanos com design inspirado na Escandinávia.',
            ],
            'image_alt' => [
                'uk' => 'Рендер {product} №{index}',
                'en' => 'Render of {product} #{index}',
                'ru' => 'Рендер {product} №{index}',
                'pt' => 'Renderização de {product} nº{index}',
            ],
        ],
        'wellness' => [
            'category' => [
                'uk' => 'Догляд та велнес',
                'en' => 'Wellness & Care',
                'ru' => 'Уход и велнес',
                'pt' => 'Bem-estar e cuidados',
            ],
            'product_noun' => [
                'uk' => 'спа-набір',
                'en' => 'spa set',
                'ru' => 'спа-набор',
                'pt' => 'kit spa',
            ],
            'product_description' => [
                'uk' => 'Ароматерапевтичний набір {brand} з морською сіллю та маслами.',
                'en' => '{brand} aromatherapy set with sea salt and botanical oils.',
                'ru' => 'Ароматерапевтический набор {brand} с морской солью и маслами.',
                'pt' => 'Kit de aromaterapia {brand} com sal marinho e óleos botânicos.',
            ],
            'vendor_suffix' => [
                'uk' => 'Студія',
                'en' => 'Studio',
                'ru' => 'Студия',
                'pt' => 'Estúdio',
            ],
            'vendor_description' => [
                'uk' => '{brand} збирає велнес-набори з натуральних інгредієнтів.',
                'en' => '{brand} curates wellness kits using natural ingredients.',
                'ru' => '{brand} создаёт велнес-наборы из натуральных ингредиентов.',
                'pt' => '{brand} cria kits de bem-estar com ingredientes naturais.',
            ],
            'image_alt' => [
                'uk' => 'Спа-набір {product} №{index}',
                'en' => '{product} spa set #{index}',
                'ru' => 'Спа-набор {product} №{index}',
                'pt' => 'Kit spa {product} nº{index}',
            ],
        ],
        'home' => [
            'category' => [
                'uk' => 'Дім та декор',
                'en' => 'Home & Decor',
                'ru' => 'Дом и декор',
                'pt' => 'Casa e decoração',
            ],
            'product_noun' => [
                'uk' => 'ароматична свічка',
                'en' => 'scented candle',
                'ru' => 'ароматическая свеча',
                'pt' => 'vela aromática',
            ],
            'product_description' => [
                'uk' => 'Соєва свічка {brand} з нотами бергамота та сірої амбри.',
                'en' => '{brand} soy candle with bergamot and grey amber notes.',
                'ru' => 'Соевая свеча {brand} с нотами бергамота и серой амбры.',
                'pt' => 'Vela de soja {brand} com notas de bergamota e âmbar cinza.',
            ],
            'vendor_suffix' => [
                'uk' => 'Майстерня',
                'en' => 'Workshop',
                'ru' => 'Мастерская',
                'pt' => 'Oficina',
            ],
            'vendor_description' => [
                'uk' => '{brand} виготовляє декор вручну з локальних матеріалів.',
                'en' => '{brand} handcrafts home decor from locally sourced materials.',
                'ru' => '{brand} изготавливает декор вручную из локальных материалов.',
                'pt' => '{brand} cria peças decorativas à mão com materiais locais.',
            ],
            'image_alt' => [
                'uk' => 'Інтерʼєр з {product} №{index}',
                'en' => 'Interior featuring {product} #{index}',
                'ru' => 'Интерьер с {product} №{index}',
                'pt' => 'Interior com {product} nº{index}',
            ],
        ],
        'outdoor' => [
            'category' => [
                'uk' => 'Аутдор та подорожі',
                'en' => 'Outdoor & Travel',
                'ru' => 'Аутдор и путешествия',
                'pt' => 'Ar livre e viagens',
            ],
            'product_noun' => [
                'uk' => 'трекінговий наплічник',
                'en' => 'trekking backpack',
                'ru' => 'треккинговый рюкзак',
                'pt' => 'mochila de trekking',
            ],
            'product_description' => [
                'uk' => 'Легкий наплічник {brand} з мембранною тканиною та кріпленням для трекінгових палиць.',
                'en' => 'Lightweight {brand} pack with membrane fabric and trekking pole mounts.',
                'ru' => 'Лёгкий рюкзак {brand} с мембранной тканью и креплением для треккинговых палок.',
                'pt' => 'Mochila {brand} leve com tecido membrana e suporte para bastões.',
            ],
            'vendor_suffix' => [
                'uk' => 'Екіпірування',
                'en' => 'Outfitters',
                'ru' => 'Снаряжение',
                'pt' => 'Equipamentos',
            ],
            'vendor_description' => [
                'uk' => '{brand} тестує спорядження у Карпатах та на узбережжі.',
                'en' => '{brand} tests gear in the Carpathians and along coastal trails.',
                'ru' => '{brand} тестирует снаряжение в Карпатах и на побережье.',
                'pt' => '{brand} testa equipamentos nos Cárpatos e em trilhas costeiras.',
            ],
            'image_alt' => [
                'uk' => 'Похід з {product} №{index}',
                'en' => 'Hike with {product} #{index}',
                'ru' => 'Поход с {product} №{index}',
                'pt' => 'Trilha com {product} nº{index}',
            ],
        ],
    ];

    private const COUPONS = [
        'welcome' => [
            'name' => [
                'uk' => 'Вітальна знижка',
                'en' => 'Welcome discount',
                'ru' => 'Приветственная скидка',
                'pt' => 'Desconto de boas-vindas',
            ],
            'description' => [
                'uk' => 'Перший купівельний бонус для знайомства з асортиментом.',
                'en' => 'First-time shopper bonus to explore the catalogue.',
                'ru' => 'Бонус для первого заказа и знакомства с ассортиментом.',
                'pt' => 'Bónus inicial para descobrir o catálogo.',
            ],
        ],
        'shipping' => [
            'name' => [
                'uk' => 'Безкоштовна доставка',
                'en' => 'Free shipping bonus',
                'ru' => 'Бесплатная доставка',
                'pt' => 'Envio gratuito',
            ],
            'description' => [
                'uk' => 'Покриває стандартну доставку для середніх замовлень.',
                'en' => 'Covers standard shipping for mid-size carts.',
                'ru' => 'Покрывает стандартную доставку для средних корзин.',
                'pt' => 'Cobre o envio padrão para compras médias.',
            ],
        ],
        'vip' => [
            'name' => [
                'uk' => 'VIP-пропозиція',
                'en' => 'VIP offer',
                'ru' => 'VIP-предложение',
                'pt' => 'Oferta VIP',
            ],
            'description' => [
                'uk' => 'Ексклюзивна знижка для постійних клієнтів.',
                'en' => 'Exclusive discount for loyal customers.',
                'ru' => 'Эксклюзивная скидка для постоянных клиентов.',
                'pt' => 'Desconto exclusivo para clientes fiéis.',
            ],
        ],
    ];

    private const WAREHOUSES = [
        'main' => [
            'name' => [
                'uk' => 'Головний склад',
                'en' => 'Main warehouse',
                'ru' => 'Главный склад',
                'pt' => 'Armazém principal',
            ],
            'description' => [
                'uk' => 'Центральний вузол обробки замовлень.',
                'en' => 'Central fulfilment hub.',
                'ru' => 'Центральный узел обработки заказов.',
                'pt' => 'Centro logístico central.',
            ],
        ],
        'eu' => [
            'name' => [
                'uk' => 'Європейський хаб',
                'en' => 'European hub',
                'ru' => 'Европейский хаб',
                'pt' => 'Hub europeu',
            ],
            'description' => [
                'uk' => 'Розподіл замовлень для ЄС.',
                'en' => 'Distributes orders across the EU.',
                'ru' => 'Распределяет заказы по ЕС.',
                'pt' => 'Distribui pedidos pela UE.',
            ],
        ],
        'us' => [
            'name' => [
                'uk' => 'Північноамериканський склад',
                'en' => 'North American warehouse',
                'ru' => 'Склад у Північній Америці',
                'pt' => 'Armazém norte-americano',
            ],
            'description' => [
                'uk' => 'Опрацьовує замовлення узбережжя США.',
                'en' => 'Handles US coastal fulfilment.',
                'ru' => 'Обслуживает заказы на побережье США.',
                'pt' => 'Processa pedidos das costas dos EUA.',
            ],
        ],
        'regional' => [
            'name' => [
                'uk' => 'Регіональний склад',
                'en' => 'Regional warehouse',
                'ru' => 'Региональный склад',
                'pt' => 'Armazém regional',
            ],
            'description' => [
                'uk' => 'Оперативні поставки у межах регіону.',
                'en' => 'Fast deliveries within the region.',
                'ru' => 'Оперативные поставки по региону.',
                'pt' => 'Entregas rápidas na região.',
            ],
        ],
    ];

    private static array $fakers = [];

    public static function supportedLocales(): array
    {
        $supported = config('app.supported_locales');
        if (is_array($supported) && $supported !== []) {
            return array_values($supported);
        }

        return [config('app.locale')];
    }

    public static function themes(): array
    {
        return array_keys(self::THEMES);
    }

    public static function productSet(?string $theme = null, ?string $brand = null): array
    {
        $theme ??= self::randomTheme();
        $brand ??= self::brand();

        return [
            'theme' => $theme,
            'brand' => $brand,
            'name' => self::productName($theme, $brand),
            'description' => self::productDescription($theme, $brand),
        ];
    }

    public static function vendorSet(?string $theme = null, ?string $brand = null): array
    {
        $theme ??= self::randomTheme();
        $brand ??= self::brand();

        return [
            'theme' => $theme,
            'brand' => $brand,
            'name' => self::vendorName($theme, $brand),
            'description' => self::vendorDescription($theme, $brand),
        ];
    }

    public static function productName(?string $theme = null, ?string $brand = null): array
    {
        $theme ??= self::randomTheme();
        $brand ??= self::brand();

        $adjective = self::random(self::ADJECTIVES);
        $noun = self::THEMES[$theme]['product_noun'];

        return self::compose([
            $adjective,
            self::uniform($brand),
            $noun,
        ]);
    }

    public static function productDescription(string $theme, string $brand): array
    {
        $template = self::THEMES[$theme]['product_description'];

        return self::fillTemplate($template, [
            'brand' => self::uniform($brand),
        ]);
    }

    public static function categoryName(string $theme): array
    {
        return self::THEMES[$theme]['category'];
    }

    public static function vendorName(string $theme, string $brand): array
    {
        $suffix = self::THEMES[$theme]['vendor_suffix'];

        return self::compose([
            self::uniform($brand),
            $suffix,
        ]);
    }

    public static function vendorDescription(string $theme, string $brand): array
    {
        $template = self::THEMES[$theme]['vendor_description'];

        return self::fillTemplate($template, [
            'brand' => self::uniform($brand),
        ]);
    }

    public static function imageAlt(string $theme, array $productName, int $index): array
    {
        $template = self::THEMES[$theme]['image_alt'];

        return self::fillTemplate($template, [
            'product' => $productName,
            'index' => self::uniform((string) $index),
        ]);
    }

    public static function couponTexts(string $key): array
    {
        return self::COUPONS[$key] ?? self::COUPONS['welcome'];
    }

    public static function warehouseTexts(string $key): array
    {
        return self::WAREHOUSES[$key] ?? self::WAREHOUSES['regional'];
    }

    public static function uniform(string $value): array
    {
        $locales = self::supportedLocales();

        return array_fill_keys($locales, $value);
    }

    public static function fillTemplate(array $templates, array $replacements): array
    {
        $result = [];

        foreach (self::supportedLocales() as $locale) {
            $text = $templates[$locale] ?? ($templates['en'] ?? reset($templates));

            foreach ($replacements as $key => $values) {
                $value = is_array($values)
                    ? ($values[$locale] ?? ($values['en'] ?? reset($values)))
                    : $values;

                $text = str_replace('{' . $key . '}', $value, $text);
            }

            $result[$locale] = $text;
        }

        return $result;
    }

    public static function compose(array $parts, string $glue = ' '): array
    {
        $result = [];

        foreach (self::supportedLocales() as $locale) {
            $segments = [];
            foreach ($parts as $part) {
                $segments[] = $part[$locale] ?? ($part['en'] ?? reset($part));
            }

            $result[$locale] = trim(implode($glue, array_filter($segments)));
        }

        return $result;
    }

    public static function brand(): string
    {
        $company = self::faker('en')->unique()->company();

        return Str::title(trim(preg_replace('/[^\pL\d\s]/u', '', $company)));
    }

    private static function randomTheme(): string
    {
        $keys = array_keys(self::THEMES);

        return $keys[array_rand($keys)];
    }

    private static function random(array $source): array
    {
        return $source[array_rand($source)];
    }

    private static function faker(string $locale): Generator
    {
        $code = self::LOCALE_MAP[$locale] ?? $locale;

        return self::$fakers[$code] ??= FakerFactory::create($code);
    }
}
