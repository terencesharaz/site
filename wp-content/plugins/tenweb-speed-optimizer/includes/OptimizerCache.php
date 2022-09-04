<?php
namespace TenWebOptimizer;


/**
 * Handles disk-cache-related operations.
 */
if (!defined('ABSPATH')) {
    exit;
}

class OptimizerCache
{
    const TWO_FASTCGI_NON_CACHED_URLS = [
        '/wp-admin/',
        '/xmlrpc.php',
        'wp-.*.php',
        'feed',
        'index.php',
        'sitemap(_index)?.xml',
        '/store.*',
        '/cart.*',
        '/my-account.*',
        '/checkout.*',
        '/addons.*'
    ];

    /* only for 10web.io, specially for Dianna */
    const TWO_FASTCGI_CACHED_URLS = [
        'wordpress-instagram-feed',
        'wordpress-facebook-feed',
    ];

    const TWO_FASTCGI_NON_CACHED_PAGES_IF_COOKIE_EXISTS = [
        'comment_author',
        'wordpress_[a-f0-9]+',
        'wp-postpass',
        'wordpress_no_cache',
        'wordpress_logged_in',
        'woocommerce_cart_hash',
        'woocommerce_items_in_cart',
        'wp_woocommerce_session',
        'woocommerce_recently_viewed',
    ];

    const TWO_FASTCGI_NON_CACHED_PAGES_IF_USER_AGENT = [
      // Source: https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json
        "Googlebot\\/",
        "Googlebot-Mobile",
        "Googlebot-Image",
        "Googlebot-News",
        "Googlebot-Video",
        "AdsBot-Google([^-]|$)",
        "AdsBot-Google-Mobile",
        "Feedfetcher-Google",
        "Mediapartners-Google",
        "Mediapartners \\(Googlebot\\)",
        "APIs-Google",
        "bingbot",
        "Slurp",
        "[wW]get",
        "LinkedInBot",
        "Python-urllib",
        "python-requests",
        "aiohttp",
        "httpx",
        "libwww-perl",
        "httpunit",
        "nutch",
        "Go-http-client",
        "phpcrawl",
        "msnbot",
        "jyxobot",
        "FAST-WebCrawler",
        "FAST Enterprise Crawler",
        "BIGLOTRON",
        "Teoma",
        "convera",
        "seekbot",
        "Gigabot",
        "Gigablast",
        "exabot",
        "ia_archiver",
        "GingerCrawler",
        "webmon ",
        "HTTrack",
        "grub.org",
        "UsineNouvelleCrawler",
        "antibot",
        "netresearchserver",
        "speedy",
        "fluffy",
        "findlink",
        "msrbot",
        "panscient",
        "yacybot",
        "AISearchBot",
        "ips-agent",
        "tagoobot",
        "MJ12bot",
        "woriobot",
        "yanga",
        "buzzbot",
        "mlbot",
        "YandexBot",
        "YandexImages",
        "YandexAccessibilityBot",
        "YandexMobileBot",
        "YandexMetrika",
        "YandexTurbo",
        "YandexImageResizer",
        "YandexVideo",
        "YandexAdNet",
        "YandexBlogs",
        "YandexCalendar",
        "YandexDirect",
        "YandexFavicons",
        "YaDirectFetcher",
        "YandexForDomain",
        "YandexMarket",
        "YandexMedia",
        "YandexMobileScreenShotBot",
        "YandexNews",
        "YandexOntoDB",
        "YandexPagechecker",
        "YandexPartner",
        "YandexRCA",
        "YandexSearchShop",
        "YandexSitelinks",
        "YandexSpravBot",
        "YandexTracker",
        "YandexVertis",
        "YandexVerticals",
        "YandexWebmaster",
        "YandexScreenshotBot",
        "purebot",
        "Linguee Bot",
        "CyberPatrol",
        "voilabot",
        "Baiduspider",
        "citeseerxbot",
        "spbot",
        "twengabot",
        "postrank",
        "TurnitinBot",
        "scribdbot",
        "page2rss",
        "sitebot",
        "linkdex",
        "Adidxbot",
        "ezooms",
        "dotbot",
        "Mail.RU_Bot",
        "discobot",
        "heritrix",
        "findthatfile",
        "europarchive.org",
        "NerdByNature.Bot",
        "sistrix crawler",
        "Ahrefs(Bot|SiteAudit)",
        "fuelbot",
        "CrunchBot",
        "IndeedBot",
        "mappydata",
        "woobot",
        "ZoominfoBot",
        "PrivacyAwareBot",
        "Multiviewbot",
        "SWIMGBot",
        "Grobbot",
        "eright",
        "Apercite",
        "semanticbot",
        "Aboundex",
        "domaincrawler",
        "wbsearchbot",
        "summify",
        "CCBot",
        "edisterbot",
        "seznambot",
        "ec2linkfinder",
        "gslfbot",
        "aiHitBot",
        "intelium_bot",
        "facebookexternalhit",
        "Yeti",
        "RetrevoPageAnalyzer",
        "lb-spider",
        "Sogou",
        "lssbot",
        "careerbot",
        "wotbox",
        "wocbot",
        "ichiro",
        "DuckDuckBot",
        "lssrocketcrawler",
        "drupact",
        "webcompanycrawler",
        "acoonbot",
        "openindexspider",
        "gnam gnam spider",
        "web-archive-net.com.bot",
        "backlinkcrawler",
        "coccoc",
        "integromedb",
        "content crawler spider",
        "toplistbot",
        "it2media-domain-crawler",
        "ip-web-crawler.com",
        "siteexplorer.info",
        "elisabot",
        "proximic",
        "changedetection",
        "arabot",
        "WeSEE:Search",
        "niki-bot",
        "CrystalSemanticsBot",
        "rogerbot",
        "360Spider",
        "psbot",
        "InterfaxScanBot",
        "CC Metadata Scaper",
        "g00g1e.net",
        "GrapeshotCrawler",
        "urlappendbot",
        "brainobot",
        "fr-crawler",
        "binlar",
        "SimpleCrawler",
        "Twitterbot",
        "cXensebot",
        "smtbot",
        "bnf.fr_bot",
        "A6-Indexer",
        "ADmantX",
        "Facebot",
        "OrangeBot\\/",
        "memorybot",
        "AdvBot",
        "MegaIndex",
        "SemanticScholarBot",
        "ltx71",
        "nerdybot",
        "xovibot",
        "BUbiNG",
        "Qwantify",
        "archive.org_bot",
        "Applebot",
        "TweetmemeBot",
        "crawler4j",
        "findxbot",
        "S[eE][mM]rushBot",
        "yoozBot",
        "lipperhey",
        "Y!J",
        "Domain Re-Animator Bot",
        "AddThis",
        "Screaming Frog SEO Spider",
        "MetaURI",
        "Scrapy",
        "Livelap[bB]ot",
        "OpenHoseBot",
        "CapsuleChecker",
        "collection@infegy.com",
        "IstellaBot",
        "DeuSu\\/",
        "betaBot",
        "Cliqzbot\\/",
        "MojeekBot\\/",
        "netEstate NE Crawler",
        "SafeSearch microdata crawler",
        "Gluten Free Crawler\\/",
        "Sonic",
        "Sysomos",
        "Trove",
        "deadlinkchecker",
        "Slack-ImgProxy",
        "Embedly",
        "RankActiveLinkBot",
        "iskanie",
        "SafeDNSBot",
        "SkypeUriPreview",
        "Veoozbot",
        "Slackbot",
        "redditbot",
        "datagnionbot",
        "Google-Adwords-Instant",
        "adbeat_bot",
        "WhatsApp",
        "contxbot",
        "pinterest.com.bot",
        "electricmonk",
        "GarlikCrawler",
        "BingPreview\\/",
        "vebidoobot",
        "FemtosearchBot",
        "Yahoo Link Preview",
        "MetaJobBot",
        "DomainStatsBot",
        "mindUpBot",
        "Daum\\/",
        "Jugendschutzprogramm-Crawler",
        "Xenu Link Sleuth",
        "Pcore-HTTP",
        "moatbot",
        "KosmioBot",
        "[pP]ingdom",
        "AppInsights",
        "PhantomJS",
        "Gowikibot",
        "PiplBot",
        "Discordbot",
        "TelegramBot",
        "Jetslide",
        "newsharecounts",
        "James BOT",
        "Bark[rR]owler",
        "TinEye",
        "SocialRankIOBot",
        "trendictionbot",
        "Ocarinabot",
        "epicbot",
        "Primalbot",
        "DuckDuckGo-Favicons-Bot",
        "GnowitNewsbot",
        "Leikibot",
        "LinkArchiver",
        "YaK\\/",
        "PaperLiBot",
        "Digg Deeper",
        "dcrawl",
        "Snacktory",
        "AndersPinkBot",
        "Fyrebot",
        "EveryoneSocialBot",
        "Mediatoolkitbot",
        "Luminator-robots",
        "ExtLinksBot",
        "SurveyBot",
        "NING\\/",
        "okhttp",
        "Nuzzel",
        "omgili",
        "PocketParser",
        "YisouSpider",
        "um-LN",
        "ToutiaoSpider",
        "MuckRack",
        "Jamie's Spider",
        "AHC\\/",
        "NetcraftSurveyAgent",
        "Laserlikebot",
        "^Apache-HttpClient",
        "AppEngine-Google",
        "Jetty",
        "Upflow",
        "Thinklab",
        "Traackr.com",
        "Twurly",
        "Mastodon",
        "http_get",
        "DnyzBot",
        "botify",
        "007ac9 Crawler",
        "BehloolBot",
        "BrandVerity",
        "check_http",
        "BDCbot",
        "ZumBot",
        "EZID",
        "ICC-Crawler",
        "ArchiveBot",
        "^LCC ",
        "filterdb.iss.net\\/crawler",
        "BLP_bbot",
        "BomboraBot",
        "Buck\\/",
        "Companybook-Crawler",
        "Genieo",
        "magpie-crawler",
        "MeltwaterNews",
        "Moreover",
        "newspaper\\/",
        "ScoutJet",
        "(^| )sentry\\/",
        "StorygizeBot",
        "UptimeRobot",
        "OutclicksBot",
        "seoscanners",
        "Hatena",
        "Google Web Preview",
        "MauiBot",
        "AlphaBot",
        "SBL-BOT",
        "IAS crawler",
        "adscanner",
        "Netvibes",
        "acapbot",
        "Baidu-YunGuanCe",
        "bitlybot",
        "blogmuraBot",
        "Bot.AraTurka.com",
        "bot-pge.chlooe.com",
        "BoxcarBot",
        "BTWebClient",
        "ContextAd Bot",
        "Digincore bot",
        "Disqus",
        "Feedly",
        "Fetch\\/",
        "Fever",
        "Flamingo_SearchEngine",
        "FlipboardProxy",
        "g2reader-bot",
        "G2 Web Services",
        "imrbot",
        "K7MLWCBot",
        "Kemvibot",
        "Landau-Media-Spider",
        "linkapediabot",
        "vkShare",
        "Siteimprove.com",
        "BLEXBot\\/",
        "DareBoost",
        "ZuperlistBot\\/",
        "Miniflux\\/",
        "Feedspot",
        "Diffbot\\/",
        "SEOkicks",
        "tracemyfile",
        "Nimbostratus-Bot",
        "zgrab",
        "PR-CY.RU",
        "AdsTxtCrawler",
        "Datafeedwatch",
        "Zabbix",
        "TangibleeBot",
        "google-xrawler",
        "axios",
        "Amazon CloudFront",
        "Pulsepoint",
        "CloudFlare-AlwaysOnline",
        "Google-Structured-Data-Testing-Tool",
        "WordupInfoSearch",
        "WebDataStats",
        "HttpUrlConnection",
        "Seekport Crawler",
        "ZoomBot",
        "VelenPublicWebCrawler",
        "MoodleBot",
        "jpg-newsbot",
        "outbrain",
        "W3C_Validator",
        "Validator\\.nu",
        "W3C-checklink",
        "W3C-mobileOK",
        "W3C_I18n-Checker",
        "FeedValidator",
        "W3C_CSS_Validator",
        "W3C_Unicorn",
        "Google-PhysicalWeb",
        "Blackboard",
        "ICBot\\/",
        "BazQux",
        "Twingly",
        "Rivva",
        "Experibot",
        "awesomecrawler",
        "Dataprovider.com",
        "GroupHigh\\/",
        "theoldreader.com",
        "AnyEvent",
        "Uptimebot\\.org",
        "Nmap Scripting Engine",
        "2ip.ru",
        "Clickagy",
        "Caliperbot",
        "MBCrawler",
        "online-webceo-bot",
        "B2B Bot",
        "AddSearchBot",
        "Google Favicon",
        "HubSpot",
        "HeadlessChrome",
        "CheckMarkNetwork\\/",
        "www\\.uptime\\.com",
        "Streamline3Bot\\/",
        "serpstatbot\\/",
        "MixnodeCache\\/",
        "SimpleScraper",
        "RSSingBot",
        "Jooblebot",
        "fedoraplanet",
        "Friendica",
        "NextCloud",
        "Tiny Tiny RSS",
        "RegionStuttgartBot",
        "Bytespider",
        "Datanyze",
        "Google-Site-Verification",
        "TrendsmapResolver",
        "tweetedtimes",
        "NTENTbot",
        "Gwene",
        "SimplePie",
        "SearchAtlas",
        "Superfeedr",
        "feedbot",
        "UT-Dorkbot",
        "Amazonbot",
        "SerendeputyBot",
        "Eyeotabot",
        "officestorebot",
        "Neticle Crawler",
        "SurdotlyBot",
        "LinkisBot",
        "AwarioSmartBot",
        "AwarioRssBot",
        "RyteBot",
        "FreeWebMonitoring SiteChecker",
        "AspiegelBot",
        "NAVER Blog Rssbot",
        "zenback bot",
        "SentiBot",
        "Domains Project\\/",
        "Pandalytics",
        "VKRobot",
        "bidswitchbot",
        "tigerbot",
        "NIXStatsbot",
        "Atom Feed Robot",
        "Curebot",
        "PagePeeker\\/",
        "Vigil\\/",
        "rssbot\\/",
        "startmebot\\/",
        "JobboerseBot",
        "seewithkids",
        "NINJA bot",
        "Cutbot",
        "BublupBot",
        "BrandONbot",
        "RidderBot",
        "Taboolabot",
        "Dubbotbot",
        "FindITAnswersbot",
        "infoobot",
        "Refindbot",
        "BlogTraffic\\/\\d\\.\\d+ Feed-Fetcher",
        "SeobilityBot",
        "Cincraw",
        "Dragonbot",
        "VoluumDSP-content-bot",
        "FreshRSS",
        "BitBot",
        "^PHP-Curl-Class",
        "Google-Certificates-Bridge",
        "centurybot",
        "Viber",
        "e\\.ventures Investment Crawler",
        "evc-batch",
        "PetalBot",
        "virustotal",

      // Source: https://www.keycdn.com/blog/web-crawlers
        'Sogou Pic Spider', 'Sogou head spider', 'Sogou web spider', 'Sogou Orion spider', 'Sogou-Test-Spider',
        'Konqueror',
        'coccocbot',
    ];

    /**
     * Cache filename.
     *
     * @var string
     */
    private $filename;
    /**
     * Cache directory path (with a trailing slash).
     *
     * @var string
     */
    private $cachedir;
    /**
     * Whether gzipping is done by the web server or us.
     * True => we don't gzip, the web server does it.
     * False => we do it ourselves.
     *
     * @var bool
     */
    private $nogzip;

    private $hashes;

    private $ext;
    private $media;

    /**
     * Ctor.
     *
     * @param string $md5 Hash.
     * @param string $ext Extension.
     */
    public function __construct($md5, $ext = 'php', $fileHashes = [], $media = "all")
    {
        $this->media = $media;
        $this->cachedir = TWO_CACHE_DIR;
        $this->nogzip = TWO_CACHE_NOGZIP;
        $this->hashes = $fileHashes;
        $this->ext = $ext;
        if (!$this->nogzip) {
            $this->filename = TWO_CACHEFILE_PREFIX . $md5 . '.php';
        } else {
            if (in_array($ext, array('js', 'css'))) {
                $this->filename = $ext . '/' . TWO_CACHEFILE_PREFIX . $md5 . '.' . $ext;
            }elseif($ext === "critical"){
              $this->filename = $ext . '/' . TWO_CACHEFILE_PREFIX . $md5 . '.css';
            }elseif ($ext === "font"){
                $this->filename = 'critical/' . TWO_CACHEFILE_PREFIX . $md5 . '.json';
            }
            else {
                $this->filename = TWO_CACHEFILE_PREFIX . $md5 . '.' . $ext;
            }
        }
    }

    //todo remove first version cache logic

    /**
     * Returns whether page should be optimized or not based on page url and fastcgi excluded pages merged with option
     *
     * @return bool
     */
    public static function urlIsOptimizable()
    {
        global $TwoSettings;
        $optimizerDisabledPages = array_filter(
            array_map('trim', explode(',', $TwoSettings->get_settings('two_disabled_speed_optimizer_pages', '')))
        );


        if ( !isset( $_SERVER['REQUEST_URI'] ) || preg_match('~\.xml|\.txt|wp-login\.php|wp-register\.php~', $_SERVER[ 'REQUEST_URI' ] ) ) {
            return false;
        }

        if (!empty($optimizerDisabledPages)) {
            //check excluded pages
            foreach ($optimizerDisabledPages as $optimizerDisabledPage) {
                if (preg_match('~' . $optimizerDisabledPage . '~', $_SERVER['REQUEST_URI'])) {
                    return false;
                }
            }
        }

        $no_optimize_pages_list = $TwoSettings->get_settings('no_optimize_pages', array());
        if(is_array($no_optimize_pages_list)){
            foreach ($no_optimize_pages_list as $no_optimize_page) {
                $no_optimize_page = str_replace("/", "", $no_optimize_page);
                if( $no_optimize_page == str_replace("/", "", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) {
                    return false;
                }
            }
        }

        // fix for first request from crawler
        if (empty($_GET) || (isset($_GET["two_preview"]) && $_GET["two_preview"]==="1")) {
            return true;
        }

        // Disable cache for bots.
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '#(' . implode( '|', self::TWO_FASTCGI_NON_CACHED_PAGES_IF_USER_AGENT ) . ')#', $_SERVER['HTTP_USER_AGENT'] ) ) {
            return false;
        }



      if (!empty($TwoSettings->get_settings('two_all_pages_are_optimizable', ''))) {
            return true;
        }

        //check explicitly cached pages
        foreach (self::TWO_FASTCGI_CACHED_URLS as $cachedUrl) {
            if (preg_match('~' . $cachedUrl . '~', $_SERVER['REQUEST_URI'])) {
                return true;
            }
        }


        //check non-cached pages
        foreach (self::TWO_FASTCGI_NON_CACHED_URLS as $nonCachedUrl) {
            if ( !isset( $_SERVER['REQUEST_URI'] ) || preg_match('~' . $nonCachedUrl . '~', $_SERVER['REQUEST_URI'])) {
                return false;
            }
        }

        //check non-cached cookies
        foreach (self::TWO_FASTCGI_NON_CACHED_PAGES_IF_COOKIE_EXISTS as $nonCachedCookieName) {
            if (!empty(OptimizerUtils::preg_grep_keys('~' . $nonCachedCookieName . '~', $_COOKIE))) {
                return false;
            }
        }

        return true;
    }


    /**
     * Check whether it is a GET REQUEST
     */
    public static function isGetRequest()
    {
        return isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Returns true if the cached file exists on disk.
     *
     * @return bool
     */
    public function check()
    {
        static $files = [];
        $files[] = $this->filename;
        if (
            !is_dir($this->cachedir) &&
            !mkdir($concurrentDirectory = $this->cachedir, 0777, true) &&
            !is_dir($concurrentDirectory) &&
            !is_writable($concurrentDirectory)
        ) {
            return false;
        }
        file_put_contents($this->cachedir . '_all_cache_files.txt', json_encode($files));

        return file_exists($this->cachedir . $this->filename);
    }

    /**
     * Returns cache contents if they exist, false otherwise.
     *
     * @return string|false
     */
    public function retrieve()
    {
        if ($this->check()) {
            if (false == $this->nogzip) {
                return file_get_contents($this->cachedir . $this->filename . '.none');
            }

            return file_get_contents($this->cachedir . $this->filename);
        }

        return false;
    }

    /**
     * Stores given $data in cache.
     *
     * @param string $data Data to cache.
     * @param string $mime Mimetype.
     *
     * @return void
     */
    public function cache($data, $mime)
    {
        self::check_and_create_dirs();
        if ($this->nogzip === false) {
            // We handle gzipping ourselves.
            $file = 'default.php';
            $phpcode = file_get_contents(TENWEB_SO_PLUGIN_DIR . 'config/' . $file);
            $phpcode = str_replace(array('%%CONTENT%%', 'exit;'), array($mime, ''), $phpcode);
            file_put_contents($this->cachedir . $this->filename, $phpcode);
            file_put_contents($this->cachedir . $this->filename . '.none', $data);
        } else {
            // Write code to cache without doing anything else.
            file_put_contents($this->cachedir . $this->filename, $data);
        }

        if (!empty($this->hashes)) {
            $cacheFile = $this->cachedir . '_cached.json';
            $oldData = "";
            if (file_exists($cacheFile)) {
                $oldData = json_decode(file_get_contents($cacheFile), true);
            }
            if (empty($oldData)) {
                $oldData = [];
            }
            $oldData[$this->filename] = array("media" => $this->media, "hashes" => $this->hashes);
            file_put_contents($cacheFile, json_encode($oldData));
        }

    }

    public static function getFileCacheSructure()
    {
        $cacheFile = TWO_CACHE_DIR . '_cached.json';
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        return array();
    }

    public static function filterThroughCache($scripts)
    {
        $cachedFiles = OptimizerCache::getFileCacheSructure();

        $result = [
            'code'    => [],
            'scripts' => $scripts
        ];
        foreach ($cachedFiles as $key => $files) {
            $scriptsToRemove = [];
            if (isset($files["hashes"])) {
                foreach ($files["hashes"] as $i => $file) {
                    if (isset($scripts[$file])) {
                        $scriptsToRemove[] = $file;
                        unset($files['hashes'][$i]);
                    }
                }
                if (empty($files["hashes"])) {
                    if (empty($result['code'][$files['media']])) {
                        $result['code'][$files['media']] = '';
                    }
                    $result['code'][$files['media']] .= file_get_contents(TWO_CACHE_DIR . $key);
                    $result['scripts'] = array_diff_key($result['scripts'], array_flip($scriptsToRemove));
                }
            }

        }

        return $result;
    }

    /**
     * Get cache filename.
     *
     * @return string
     */
    public function getname()
    {
        return $this->filename;
    }

    protected static function is_valid_cache_file($dir, $file)
    {
        //check if is valid file

        return '.' !== $file && '..' !== $file && false !== strpos($file, TWO_CACHEFILE_PREFIX)
            && is_file($dir . $file);
    }

    /**
     * Clears contents of TWO_CACHE_DIR.
     *
     * @return void
     */
    protected static function clear_cache_classic()
    {
        $contents = self::get_cache_contents();
        foreach ($contents as $name => $files) {
            $dir = rtrim(TWO_CACHE_DIR . $name, '/') . '/';
            foreach ($files as $file) {
                if (self::is_valid_cache_file($dir, $file)) {
                    @unlink($dir . $file); // @codingStandardsIgnoreLine
                }
            }
        }
        @unlink(TWO_CACHE_DIR . '/.htaccess'); // @codingStandardsIgnoreLine
    }

    /**
     * Recursively deletes the specified pathname (file/directory) if possible.
     * Returns true on success, false otherwise.
     *
     * @param string $pathname Pathname to remove.
     *
     * @return bool
     */
    protected static function rmdir($pathname)
    {
        $files = self::get_dir_contents($pathname);
        foreach ($files as $file) {
            $path = $pathname . '/' . $file;
            if (is_dir($path)) {
                self::rmdir($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($pathname);
    }

    /**
     * Clears contents of TWO_CACHE_DIR by renaming the current
     * cache directory into a new one with a unique name and then
     * re-creating the default (empty) cache directory.
     *
     * @return bool Returns true when everything is done successfully, false otherwise.
     */
    protected static function clear_cache_via_rename()
    {
        $ok = false;
        $dir = self::get_pathname_base();
        $new_name = self::get_unique_name();
        // Makes sure the new pathname is on the same level...
        $new_pathname = dirname($dir) . '/' . $new_name;
        $renamed = @rename($dir, $new_pathname); // @codingStandardsIgnoreLine

        return $ok;
    }

    /**
     * Returns a (hopefully) unique new cache folder name for renaming purposes.
     *
     * @return string
     */
    protected static function get_unique_name()
    {
        $prefix = self::get_advanced_cache_clear_prefix();

        return uniqid($prefix, true);
    }

    /**
     * Get cache prefix name used in advanced cache clearing mode.
     *
     * @return string
     */
    protected static function get_advanced_cache_clear_prefix()
    {
        $pathname = self::get_pathname_base();
        $basename = basename($pathname);

        return $basename . '-';
    }

    /**
     * Returns an array of file and directory names found within
     * the given $pathname without '.' and '..' elements.
     *
     * @param string $pathname Pathname.
     *
     * @return array
     */
    protected static function get_dir_contents($pathname)
    {
        return array_slice(scandir($pathname), 2);
    }

    /**
     * Wipes directories which were created as part of the fast cache clearing
     * routine (which renames the current cache directory into a new one with
     * a custom-prefixed unique name).
     *
     * @return bool
     */
    public static function delete_advanced_cache_clear_artifacts()
    {
        $dir = self::get_pathname_base();
        $prefix = self::get_advanced_cache_clear_prefix();
        $parent = dirname($dir);
        $ok = false;
        // Returns the list of files without '.' and '..' elements.
        $files = self::get_dir_contents($parent);
        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $path = $parent . '/' . $file;
                $prefixed = (false !== strpos($path, $prefix));
                // Removing only our own (prefixed) directories...
                if (is_dir($path) && $prefixed) {
                    $ok = self::rmdir($path);
                }
            }
        }

        return $ok;
    }

    public static function get_path($getBase = true)
    {
        $pathname = self::get_pathname_base();

        if (is_multisite()) {
            $blog_id = get_current_blog_id();
            $pathname .= $blog_id . '/';
        }

        if ($getBase) {
            return $pathname;
        }

        return $pathname;
    }

    /**
     * Returns the base path of our cache directory.
     *
     * @return string
     */
    protected static function get_pathname_base()
    {
        return WP_CONTENT_DIR . TENWEB_SO_CACHE_CHILD_DIR;
    }

    protected static function get_cache_contents()
    {
        $contents = array();
        foreach (array('', 'js', 'css') as $dir) {
            $contents[$dir] = scandir(TWO_CACHE_DIR . $dir);
        }

        return $contents;
    }

    /**
     * Performs a scan of cache directory contents and returns an array
     * with 3 values: count, size, timestamp.
     * count = total number of found files
     * size = total filesize (in bytes) of found files
     * timestamp = unix timestamp when the scan was last performed/finished.
     *
     * @return array
     */
    protected static function stats_scan()
    {
        $count = 0;
        $size = 0;
        // Scan everything in our cache directories.
        foreach (self::get_cache_contents() as $name => $files) {
            $dir = rtrim(TWO_CACHE_DIR . $name, '/') . '/';
            foreach ($files as $file) {
                if (self::is_valid_cache_file($dir, $file)) {
                    if (TWO_CACHE_NOGZIP && (false !== strpos($file, '.js') || false !== strpos($file, '.css') || false !== strpos($file, '.img') || false !== strpos($file, '.txt'))) {
                        // Web server is gzipping, we count .js|.css|.img|.txt files.
                        $count++;
                    } else if (!TWO_CACHE_NOGZIP && false !== strpos($file, '.none')) {
                        // We are gzipping ourselves via php, counting only .none files.
                        $count++;
                    }
                    $size += filesize($dir . $file);
                }
            }
        }

        return array($count, $size, time());
    }

    /**
     * Checks if cache dirs exist and create if not.
     * Returns false if not succesful.
     *
     * @return bool
     */
    public static function check_and_create_dirs()
    {
        if (!defined('TWO_CACHE_DIR')) {
            // We didn't set a cache.
            return false;
        }
        foreach (array('', 'js', 'css', 'critical') as $dir) {
            if (!self::check_cache_dir(TWO_CACHE_DIR . $dir)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensures the specified `$dir` exists and is writeable.
     * Returns false if that's not the case.
     *
     * @param string $dir Directory to check/create.
     *
     * @return bool
     */
    protected static function check_cache_dir($dir)
    {
        // Try creating the dir if it doesn't exist.
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                return false;
            } // @codingStandardsIgnoreLine
            if (!file_exists($dir)) {
                return false;
            }
        }
        // If we still cannot write, bail.
        if (!is_writable($dir)) {
            return false;
        }
        // Create an index.html in there to avoid prying eyes!
        $idx_file = rtrim($dir, '/\\') . '/index.html';
        if (!is_file($idx_file)) {
            @file_put_contents($idx_file, '<html><head><meta name="robots" content="noindex, nofollow"></head><body></body></html>');
        }

        return true;
    }
}
