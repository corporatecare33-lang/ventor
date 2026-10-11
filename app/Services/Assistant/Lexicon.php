<?php

namespace App\Services\Assistant;

/**
 * Words the shop assistant understands, in English, Banglish (Bangla typed in Latin letters) and Bangla.
 * Every list is compared with normalised text (see normalize()), so entries can be written naturally.
 */
final class Lexicon
{
    /** Spellings mapped onto the catalogue's English words before searching. */
    public const SYNONYMS = [
        // Products
        'purse' => 'wallet', 'manibag' => 'wallet', 'moneybag' => 'wallet', 'walet' => 'wallet', 'wallat' => 'wallet',
        'ওয়ালেট' => 'wallet', 'মানিব্যাগ' => 'wallet', 'পার্স' => 'wallet',
        'cardholder' => 'card holder', 'কার্ড' => 'card', 'হোল্ডার' => 'holder', 'keyholder' => 'key holder',
        'keychain' => 'key', 'keyring' => 'key', 'chabi' => 'key', 'চাবি' => 'key', 'চাবির' => 'key',
        'পাসপোর্ট' => 'passport', 'ব্যাগ' => 'bag', 'ব্যাকপ্যাক' => 'backpack', 'টোট' => 'tote', 'ল্যাপটপ' => 'laptop',
        'উপহার' => 'gift', 'গিফট' => 'gift', 'present' => 'gift', 'presents' => 'gift',
        'চামড়া' => 'leather', 'চামড়ার' => 'leather', 'chamra' => 'leather', 'chamrar' => 'leather',
        'ট্র্যাকার' => 'tracker', 'airtag' => 'tracker', 'স্মার্ট' => 'smart',
        // Colours
        'কালো' => 'black', 'kalo' => 'black', 'বাদামি' => 'brown', 'badami' => 'brown', 'খয়েরি' => 'brown', 'khoyeri' => 'brown',
        'নীল' => 'blue', 'nil' => 'blue', 'সবুজ' => 'green', 'sobuj' => 'green', 'লাল' => 'red', 'lal' => 'red',
        'সাদা' => 'white', 'sada' => 'white', 'ধূসর' => 'grey', 'gray' => 'grey', 'মেরুন' => 'burgundy', 'maroon' => 'burgundy',
    ];

    /** Everyday colour words, used to notice a colour that no product comes in. */
    public const COLOUR_WORDS = [
        'black', 'brown', 'tan', 'red', 'blue', 'navy', 'green', 'white', 'grey', 'ivory', 'burgundy', 'pink', 'yellow',
        'orange', 'purple', 'beige', 'cream', 'gold', 'silver', 'maroon', 'olive', 'khaki', 'camel', 'cognac',
    ];

    /** Words that carry no search meaning on their own. */
    public const STOP_WORDS = [
        'a', 'an', 'the', 'i', 'im', 'me', 'my', 'mine', 'we', 'us', 'our', 'you', 'your', 'it', 'its', 'is', 'are', 'am', 'was', 'be',
        'do', 'does', 'did', 'can', 'could', 'would', 'should', 'will', 'shall', 'may', 'might', 'please', 'pls', 'plz', 'kindly',
        'show', 'see', 'view', 'find', 'search', 'get', 'give', 'tell', 'want', 'wanna', 'need', 'looking', 'look', 'like', 'love', 'have', 'has', 'got',
        'for', 'some', 'any', 'all', 'of', 'to', 'in', 'on', 'at', 'with', 'and', 'or', 'but', 'about', 'from', 'by', 'as', 'so', 'than', 'then',
        'what', 'whats', 'which', 'who', 'how', 'many', 'much', 'this', 'that', 'these', 'those', 'there', 'here', 'one', 'ones', 'also', 'just',
        'really', 'very', 'nice', 'good', 'best', 'great', 'something', 'thing', 'things', 'stuff', 'item', 'items', 'product', 'products',
        'collection', 'collections', 'range', 'category', 'categories', 'option', 'options', 'available', 'buy', 'purchase', 'shop', 'store',
        'price', 'prices', 'priced', 'cost', 'costs', 'under', 'below', 'less', 'within', 'upto', 'up', 'over', 'above', 'more', 'least',
        'between', 'around', 'approx', 'tk', 'taka', 'bdt', 'only', 'cheap', 'cheaper', 'cheapest', 'budget', 'affordable',
        'expensive', 'premium', 'luxury', 'popular', 'bestseller', 'bestsellers', 'seller', 'sellers', 'selling', 'top', 'trending', 'new',
        'newest', 'latest', 'arrival', 'arrivals', 'rated', 'rating', 'colour', 'color', 'colours', 'colors', 'stock', 'add', 'cart',
        'order', 'details', 'detail', 'info', 'information', 'similar', 'other', 'others', 'another', 'else', 'hi', 'hello', 'hey', 'yes', 'no', 'ok', 'okay',
        // Banglish
        'ami', 'amar', 'amake', 'apni', 'apnar', 'apnader', 'tumi', 'tomar', 'ki', 'ache', 'ase', 'achhe', 'acche', 'dekhan', 'dekhao',
        'dekhte', 'dekhi', 'chai', 'chacchi', 'lagbe', 'den', 'dao', 'diye', 'koto', 'dam', 'daam', 'er', 'ar', 'o', 'ta', 'ti', 'te', 'gula', 'gulo', 'guli',
        'kichu', 'kono', 'jonno', 'jnno', 'ekta', 'ekti', 'koren', 'korun', 'kore', 'valo', 'bhalo', 'moddhe', 'modhe', 'niche', 'nice', 'upore',
        'opore', 'beshi', 'kom', 'sosta', 'theke', 'porjonto', 'eta', 'ota', 'eita', 'oita', 'kemon', 'hobe', 'jabe', 'pabo', 'kinte', 'kinbo', 'nibo',
        // Bangla
        'আমি', 'আমার', 'আমাকে', 'আপনি', 'আপনার', 'আপনাদের', 'কি', 'কী', 'আছে', 'দেখান', 'দেখাও', 'দেখতে', 'চাই', 'লাগবে', 'দিন', 'দাও', 'কত',
        'দাম', 'এর', 'আর', 'ও', 'টা', 'টি', 'গুলো', 'কিছু', 'কোনো', 'জন্য', 'একটা', 'একটি', 'করুন', 'করেন', 'ভালো', 'মধ্যে', 'নিচে', 'উপরে',
        'বেশি', 'কম', 'সস্তা', 'থেকে', 'পর্যন্ত', 'টাকা', 'টাকার', 'এটা', 'ওটা', 'কেমন', 'হবে', 'যাবে', 'পাবো', 'কিনতে', 'কিনব', 'নিব', 'নতুন',
    ];

    /** Phrases that switch a product search into a hard filter. */
    public const FEATURES = [
        'nfc' => ['nfc', 'tap', 'contactless', 'এনএফসি'],
        'rfid' => ['rfid', 'rfid blocking', 'anti theft', 'anti skimming', 'blocking'],
        'engraving' => ['engrave', 'engraved', 'engraving', 'engravable', 'personalised', 'personalized', 'personalise', 'personalize', 'customised', 'customized', 'custom name', 'name engraving', 'nam lekha', 'naam lekha', 'খোদাই', 'নাম লেখা'],
        'sale' => ['sale', 'on sale', 'discount', 'discounted', 'offer', 'offers', 'deal', 'deals', 'ছাড়', 'অফার', 'ডিসকাউন্ট', 'chhar'],
    ];

    /** Phrases that choose the order of a product list. */
    public const SORTS = [
        'price_asc' => ['cheap', 'cheaper', 'cheapest', 'budget', 'affordable', 'lowest price', 'low price', 'kom dam', 'kom dame', 'sosta', 'কম দাম', 'কম দামে', 'সস্তা'],
        'price_desc' => ['expensive', 'premium', 'luxury', 'highest price', 'most expensive', 'beshi dam', 'দামি'],
        'popular' => ['best seller', 'best sellers', 'bestseller', 'bestsellers', 'best selling', 'popular', 'most popular', 'trending', 'top selling', 'most sold', 'জনপ্রিয়'],
        'newest' => ['new', 'newest', 'latest', 'new arrival', 'new arrivals', 'notun', 'নতুন'],
        'rating' => ['top rated', 'best rated', 'highest rated', 'best reviewed'],
    ];

    public const GREETING = ['hi', 'hii', 'hello', 'helo', 'hlw', 'hey', 'salam', 'assalamualaikum', 'assalamu alaikum', 'asalamualaikum', 'salamualaikum', 'good morning', 'good afternoon', 'good evening', 'হাই', 'হ্যালো', 'হেলো', 'সালাম', 'আসসালামু আলাইকুম', 'আসসালামুয়ালাইকুম'];

    public const THANKS = ['thanks', 'thank you', 'thank u', 'thx', 'tnx', 'ty', 'dhonnobad', 'dhonnobaad', 'thanks a lot', 'ধন্যবাদ'];

    public const HELP = ['help', 'what can you do', 'who are you', 'how does this work', 'menu', 'options', 'ki korte paren', 'ki ki korte paren', 'সাহায্য', 'হেল্প'];

    public const ADD = ['add', 'add to cart', 'add it', 'put in cart', 'cart e add', 'cart a add', 'carte add', 'add koro', 'add koren', 'add korun', 'যোগ করুন', 'যোগ করো', 'কার্টে', 'অ্যাড'];

    public const CART = ['cart', 'my cart', 'basket', 'my basket', 'kart', 'কার্ট', 'আমার কার্ট'];

    public const CHECKOUT = ['checkout', 'check out', 'place order', 'place my order', 'place the order', 'confirm order', 'confirm my order', 'order korbo', 'order dibo', 'order korte chai', 'order confirm', 'চেকআউট', 'অর্ডার করব', 'অর্ডার করবো', 'অর্ডার দিব', 'অর্ডার দিবো', 'অর্ডার করতে চাই'];

    public const TRACK = ['track', 'tracking', 'track order', 'order status', 'where is my order', 'my order', 'my orders', 'order kothay', 'order koi', 'amar order', 'parcel', 'ট্র্যাক', 'আমার অর্ডার', 'অর্ডার কোথায়', 'অর্ডার কই', 'অর্ডারের অবস্থা'];

    public const DELIVERY = ['delivery', 'deliver', 'shipping', 'ship', 'courier', 'delivery charge', 'shipping cost', 'how long', 'koto din', 'kobe pabo', 'kobe pab', 'inside dhaka', 'outside dhaka', 'dhakar baire', 'ডেলিভারি', 'কুরিয়ার', 'শিপিং', 'কত দিন', 'কবে পাব', 'কবে পাবো', 'ঢাকার বাইরে'];

    public const PAYMENT = ['payment', 'pay', 'paying', 'bkash', 'nagad', 'rocket', 'cod', 'cash on delivery', 'card payment', 'visa', 'mastercard', 'emi', 'পেমেন্ট', 'বিকাশ', 'নগদ', 'ক্যাশ অন ডেলিভারি'];

    public const RETURNS = ['return', 'returns', 'returned', 'returnable', 'exchange', 'refund', 'replace', 'replacement', 'ferot', 'ferat', 'change korte', 'ফেরত', 'এক্সচেঞ্জ', 'রিফান্ড', 'বদল'];

    public const WARRANTY = ['warranty', 'guarantee', 'guaranty', 'waranty', 'ওয়ারেন্টি', 'গ্যারান্টি'];

    public const NFC_INFO = ['how does nfc work', 'how nfc works', 'how does tap work', 'what is nfc', 'what is tap', 'what is leatherx tap', 'nfc ki', 'tap ki', 'nfc profile', 'tap profile', 'need an app', 'battery'];

    public const MATERIAL = ['genuine', 'genuine leather', 'real leather', 'original leather', 'pure leather', 'material', 'materials', 'made of', 'pu', 'synthetic', 'fake leather', 'asol chamra', 'khati chamra', 'আসল চামড়া', 'খাঁটি চামড়া'];

    public const CORPORATE = ['corporate', 'bulk', 'wholesale', 'company gift', 'office gift', 'employees', 'staff gift', 'client gift', 'কর্পোরেট', 'পাইকারি'];

    public const CONTACT = ['contact', 'phone number', 'call', 'call you', 'whatsapp', 'email', 'human', 'agent', 'real person', 'customer care', 'customer service', 'customer support', 'talk to', 'address', 'location', 'showroom', 'outlet', 'shop address', 'যোগাযোগ', 'ফোন', 'নাম্বার', 'হোয়াটসঅ্যাপ', 'ঠিকানা', 'শোরুম'];

    public const COUPON = ['coupon', 'promo', 'promo code', 'voucher', 'discount code', 'কুপন'];

    public const BROWSE = ['all products', 'all items', 'everything', 'catalog', 'catalogue', 'what do you sell', 'what do you have', 'sob product', 'sob dekhan', 'সব প্রোডাক্ট', 'কি কি আছে'];

    public const MORE = ['more', 'show more', 'see more', 'next', 'aro', 'aro dekhan', 'আরো', 'আরও'];

    public const QUESTION = ['how', 'what', 'why', 'when', 'does', 'is', 'can', 'do', 'which', 'koto', 'kivabe', 'kibhabe', 'ki', 'keno', 'kobe', 'কিভাবে', 'কীভাবে', 'কেন', 'কবে', 'কত', 'কি', 'কী'];

    /** Words that point back at the product being discussed. */
    public const POINTERS = ['it', 'this', 'that', 'this one', 'that one', 'same', 'eta', 'ota', 'eita', 'oita', 'etai', 'otai', 'এটা', 'ওটা', 'এটি', 'ওটি', 'এইটা', 'ওইটা'];

    /** Ordinal words → index into the last list (-1 = last). */
    public const ORDINALS = [
        'first' => 0, '1st' => 0, 'prothom' => 0, 'প্রথম' => 0, 'প্রথমটা' => 0,
        'second' => 1, '2nd' => 1, 'ditio' => 1, 'dwitiyo' => 1, 'দ্বিতীয়' => 1, 'দ্বিতীয়টা' => 1,
        'third' => 2, '3rd' => 2, 'tritio' => 2, 'তৃতীয়' => 2, 'তৃতীয়টা' => 2,
        'fourth' => 3, '4th' => 3, 'chaturtho' => 3, 'চতুর্থ' => 3, 'চতুর্থটা' => 3,
        'last' => -1, 'shesh' => -1, 'sesh' => -1, 'শেষ' => -1, 'শেষেরটা' => -1,
    ];

    public const NUMBER_WORDS = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6,
        'ek' => 1, 'dui' => 2, 'tin' => 3, 'ekta' => 1, 'duita' => 2, 'tinta' => 3, 'charta' => 4, 'pachta' => 5,
        'এক' => 1, 'দুই' => 2, 'তিন' => 3, 'একটা' => 1, 'দুইটা' => 2, 'দুটো' => 2, 'তিনটা' => 3, 'চারটা' => 4, 'পাঁচটা' => 5,
    ];

    /** Bangla endings stripped when a word isn't known as written (ওয়ালেটগুলো → ওয়ালেট). */
    private const BANGLA_SUFFIXES = ['গুলো', 'গুলি', 'গুলা', 'খানা', 'টা', 'টি', 'ের', 'এর', 'র', 'তে', 'কে', 'ে'];

    /**
     * Lower case, Bangla digits as 0-9, one form for Bangla nukta letters, digit grouping commas
     * removed, everything that isn't a letter or number turned into single spaces.
     */
    public static function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = strtr($text, [
            '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
            // Precomposed ড় ঢ় য় → base letter + nukta, the form keyboards and Unicode normalisation produce.
            "\u{09DC}" => "\u{09A1}\u{09BC}", "\u{09DD}" => "\u{09A2}\u{09BC}", "\u{09DF}" => "\u{09AF}\u{09BC}",
            '৳' => ' tk ', '’' => '', "'" => '',
        ]);
        $text = preg_replace('/(?<=\d),(?=\d{3})/u', '', $text);
        $text = preg_replace_callback('/(\d+(?:\.\d+)?)\s?k\b/u', fn ($m) => (string) (int) round((float) $m[1] * 1000), $text); // 3k, 2.5k
        $text = preg_replace('/(\d)(tk|taka)\b/u', '$1 tk', $text);
        $text = preg_replace('/[^\pL\pN\pM]+/u', ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /** "wallets" → "wallet", "accessories" → "accessory"; short words and "-ss" words stay as they are. */
    public static function singular(string $word): string
    {
        if (mb_strlen($word) <= 3 || ! preg_match('/^[a-z]+$/', $word)) {
            return $word;
        }

        return match (true) {
            str_ends_with($word, 'ies') => substr($word, 0, -3).'y',
            str_ends_with($word, 'sses'), str_ends_with($word, 'xes') => substr($word, 0, -2),
            str_ends_with($word, 'ss'), str_ends_with($word, 'us'), str_ends_with($word, 'is') => $word,
            str_ends_with($word, 's') => substr($word, 0, -1),
            default => $word,
        };
    }

    /** Synonym (or a Bangla word with its ending removed) for one normalised word, or null. */
    public static function synonym(string $word): ?string
    {
        $synonyms = self::map('synonyms', self::SYNONYMS);

        if (isset($synonyms[$word])) {
            return $synonyms[$word];
        }

        if (preg_match('/\p{Bengali}/u', $word)) {
            foreach (self::BANGLA_SUFFIXES as $suffix) {
                if (mb_strlen($word) > mb_strlen($suffix) + 1 && str_ends_with($word, $suffix)) {
                    $stem = mb_substr($word, 0, -mb_strlen($suffix));
                    if (isset($synonyms[$stem])) {
                        return $synonyms[$stem];
                    }
                }
            }
        }

        return null;
    }

    public static function isStopWord(string $word): bool
    {
        return isset(self::map('stop', array_fill_keys(self::STOP_WORDS, true))[$word]);
    }

    /**
     * True when the normalised text contains one of the phrases as whole words.
     *
     * @param  array<int, string>  $phrases
     */
    public static function contains(string $text, array $phrases): bool
    {
        $haystack = ' '.$text.' ';
        foreach ($phrases as $phrase) {
            if (str_contains($haystack, ' '.self::normalize($phrase).' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Phrase lists normalised once per request, so lexicon entries can be written as they are typed.
     *
     * @param  array<string, mixed>  $list
     * @return array<string, mixed>
     */
    private static function map(string $name, array $list): array
    {
        static $cache = [];

        return $cache[$name] ??= collect($list)->mapWithKeys(fn ($value, $key) => [self::normalize((string) $key) => $value])->all();
    }
}
