<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Scopes\hasSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Stichoza\GoogleTranslate\GoogleTranslate;
use GuzzleHttp\Client;

#[ScopedBy([hasSubscription::class])]
class Product extends Model
{
    use HasFactory;

    // protected $guarded = ['id'];
   protected $fillable = [
        'shop_id',
        'name',
        'description',
        'short_description',
        'brand_id',
        'unit_id',
        'price',
        'discount_price',
        'min_order_quantity',
        'media_id',
        'code',
        'buy_price',
        'is_active',
        'is_digital',
        'is_new',
        'is_approve',
        'video_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'carton_contents',
        'carton_units_count',
        'carton_price',

    ];
    protected $appends = ['thumbnail'];
    // protected $fillable = ['carton_contents'];
    /**
     * Retrieve the shop that this model belongs to.
     *
     * @return BelongsTo The shop that this model belongs to.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * get the translations that owns the product.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class , 'product_id');
    }
    

    public function productBranches(): HasMany
    {
        return $this->hasMany(ProductBranch::class, 'product_id');
    }

    public function quantities(): HasMany
    {
        return $this->hasMany(ProductBranch::class, 'product_id');
    }

    public function quantityBranch($branchId)
    {
        return $this->productBranches()->where('branch_id', $branchId)->first();
    }
    
    public function getTranslatedNameAttribute()
    {
        $lang = app()->getLocale();
        
        $language = $this->translations()->where('lang' , $lang)->first();
        
        return $language ??  $this->name;
    }
    public function getTranslatedARNameAttribute()
    {

        $language = $this->translations()->where('lang' , 'ar')->first();
        
        return $language ??  $this->name;
    }

    public function transl( $lang )
    {

        $language = $this->translations()->where('lang' , $lang)->first();
        
        return $language ??  $this->name;
    }

    /**
     * Retrieve the categories associated with the current model.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Retrieve the categories associated with the current model.
     */
    public function subcategories(): BelongsToMany
    {
        return $this->belongsToMany(SubCategory::class, 'product_subcategories');
    }

    /**
     * Retrieve the flash sales associated with the model.
     *
     * @return BelongsToMany The flash sales associated with the model.
     */
    public function flashSales(): BelongsToMany
    {
        $currentDateTime = Carbon::now();

        return $this->belongsToMany(FlashSale::class, 'flash_sale_products', 'product_id', 'flash_sale_id')
            ->withPivot('branch_id', 'price', 'quantity', 'discount', 'sale_quantity')
            ->where('status', 1)
            ->where(function ($query) use ($currentDateTime) {
                $query->where('start_date', '<=', $currentDateTime->toDateString())
                    ->where('end_date', '>=', $currentDateTime->toDateString())
                    ->where(function ($query) use ($currentDateTime) {
                        $query->where('start_time', '<=', $currentDateTime->toTimeString())
                            ->orWhere('end_time', '>=', $currentDateTime->toTimeString());
                    });
            })->latest('id');
    }

    public function activeFlashSale($branchId = null): ?FlashSale
    {
        $query = $this->flashSales();

        if (filled($branchId)) {
            $query->wherePivot('branch_id', $branchId);
        }

        return $query->first();
    }

    /**
     * Get the video media record associated with the model.
     *
     * @return BelongsTo The video media record associated with the model.
     */
    public function videoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_id');
    }

    /**
     * Retrieves the video associated with the model.
     *
     * @return Attribute The video attribute.
     */
    public function video(): Attribute
    {
        $video = null;

        if ($this->videoMedia && $this->videoMedia->type == 'file' && Storage::exists($this->videoMedia->src)) {
            $video = (object) [
                'id' => $this->videoMedia->id,
                'url' => Storage::url($this->videoMedia->src),
                'type' => $this->videoMedia->type,
            ];
        } elseif ($this->videoMedia && $this->videoMedia->type != 'file' && $this->videoMedia->src != null) {
            $video = (object) [
                'id' => $this->videoMedia->id,
                'url' => $this->videoMedia->src,
                'type' => $this->videoMedia->type,
            ];
        }

        return new Attribute(
            get: fn() => $video
        );
    }

    /**
     * Get the media record associated with the model.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Create a thumbnail for the media, with a default image if none is present.
     */
    public function thumbnail(): Attribute
    {
        $thumbnail = asset('default/default.jpg');
        if ($this->media && Storage::exists($this->media->src)) {
            $thumbnail = Storage::url($this->media->src);
        }

        return new Attribute(
            get: fn() => $thumbnail
        );
    }

    /**
     * Get the medias for the product.
     */
    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_thumbnails');
    }

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'product_attachments');
    }

    public function licenses() : HasMany
    {
        return $this->hasMany(ProductLicense::class);
    }

    /**
     * Generate thumbnails for the medias.
     */
    public function thumbnails(): Collection
    {
        $thumbnails = collect([]);

        if (request()->is('api/*')) {
            if ($this->videoMedia && $this->videoMedia->type == 'file' && Storage::exists($this->videoMedia->src)) {
                $thumbnails[] = (object) [
                    'id' => $this->videoMedia->id,
                    'thumbnail' => null,
                    'url' => Storage::url($this->videoMedia->src),
                    'type' => $this->videoMedia->type,
                ];
            } elseif ($this->videoMedia && $this->videoMedia->type != 'file' && $this->videoMedia->src != null) {
                $thumbnails[] = (object) [
                    'id' => $this->videoMedia->id,
                    'thumbnail' => null,
                    'url' => $this->videoMedia->src,
                    'type' => $this->videoMedia->type,
                ];
            }

            $thumbnails[] = (object) [
                'id' => $this->media?->id,
                'thumbnail' => $this->thumbnail,
                'url' => null,
                'type' => 'image',
            ];
        }

        foreach ($this->medias as $media) {
            $thumbnail = asset('default/default.jpg');
            if ($media && Storage::exists($media->src)) {
                $thumbnail = Storage::url($media->src);
            }
            $thumbnails[] = (object) [
                'id' => $media?->id,
                'thumbnail' => $thumbnail,
                'url' => null,
                'type' => 'image',
            ];
        }

        return $thumbnails;
    }

    /**
     * Generate additional thumbnails for the medias.
     */
    public function additionalAttachments(): Collection
    {
        $attachments = collect([]);
        foreach ($this->attachments as $attachment) {
            $file = asset('default/default.jpg');
            if ($attachment && Storage::exists($attachment->src)) {
                $file = Storage::url($attachment->src);
            }
            $attachments[] = (object) [
                'id' => $attachment?->id,
                'url' => $file,
                'extension' => strtolower(pathinfo($attachment->src, PATHINFO_EXTENSION)),
            ];
        }

        return $attachments;
    }

    public function additionalThumbnails(): Collection
    {
        $thumbnails = collect([]);
        foreach ($this->medias as $media) {
            $thumbnail = asset('default/default.jpg');
            if ($media && Storage::exists($media->src)) {
                $thumbnail = Storage::url($media->src);
            }
            $thumbnails[] = (object) [
                'id' => $media?->id,
                'thumbnail' => $thumbnail,
            ];
        }

        return $thumbnails;
    }

    /**
     * Retrieves the reviews associated with this object.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany The reviews associated with this object.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Calculates the average rating of the reviews.
     *
     * @return Attribute The average rating attribute.
     */
    public function averageRating(): Attribute
    {
        $avgRating = $this->reviews()->avg('rating');

        return new Attribute(
            get: fn() => (float) number_format($avgRating > 0 ? $avgRating : 0, 1, '.', '')
        );
    }

    /**
     * Retrieves the colors associated with this model.
     */
    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_colors')->withPivot('price', 'product_id');
    }

    /**
     * sizes function.
     */
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_sizes')->withPivot('price', 'product_id');
    }

    /**
     * get the brand that owns the product.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * get the unit that owns the product.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Retrieve the orders associated with the model.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_products')->withPivot('quantity', 'color', 'unit', 'size', 'price');
    }

    /**
     * Filter the given builder by active status.
     *
     * @param  Builder  $builder  The builder to filter.
     * @return Builder The filtered builder.
     */
    public function scopeIsActive(Builder $builder)
    {
        return $builder->where('is_active', true)->where('is_approve', true)->whereHas('shop', function ($query) {
            return $query->whereHas('user', function ($query) {
                $query->where('is_active', 1);
            });
        });
    }

    /**
     * Calculate the discount percentage based on the given price and discount price.
     */
    public static function getDiscountPercentage($price, $discountPrice)
    {
        return $discountPrice ? ($price - $discountPrice) * 100 / $price : 0;
    }

    /**
     * get the favorites from the model.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Translate text between Arabic and English with caching and multiple fallbacks.
     */
    protected static function translateQueryText(string $text, string $targetLang): ?string
    {
        $cacheKey = 'search_trans_' . $targetLang . '_' . md5(mb_strtolower($text));

        return Cache::remember($cacheKey, 86400, function () use ($text, $targetLang) {
            // First attempt: Stichoza GoogleTranslate
            try {
                $tr = new GoogleTranslate($targetLang, null, [
                    'timeout' => 2,
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ]
                ]);
                $result = $tr->translate($text);
                if (!empty($result) && mb_strtolower($result) !== mb_strtolower($text)) {
                    return $result;
                }
            } catch (\Throwable $e) {
                // Silently try fallback
            }

            // Second attempt: MyMemory free API
            try {
                $sourceLang = ($targetLang === 'en') ? 'ar' : 'en';
                $client = new Client(['timeout' => 2]);
                $response = $client->get('https://api.mymemory.translated.net/get', [
                    'query' => [
                        'q' => $text,
                        'langpair' => "{$sourceLang}|{$targetLang}"
                    ]
                ]);
                $data = json_decode($response->getBody(), true);
                $result = $data['responseData']['translatedText'] ?? null;
                if (!empty($result) && mb_strtolower($result) !== mb_strtolower($text)) {
                    return $result;
                }
            } catch (\Throwable $e) {
                // Silently ignore
            }

            return null;
        });
    }

    /**
     * Scope a query to search products by name or code in Arabic and English (multilingual and translated).
     */
    public function scopeSearchTranslated(Builder $builder, string $search): Builder
    {
        $search = trim($search);
        if ($search === '') {
            return $builder;
        }

        // Detect if query contains Arabic characters
        $isArabic = (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $search);
        $targetLang = $isArabic ? 'en' : 'ar';

        // Translate if possible
        $translated = self::translateQueryText($search, $targetLang);

        $phrases = [$search];
        if (!empty($translated)) {
            $phrases[] = $translated;
        }

        // Arabic normalization helper function
        $normalizeArabic = function (string $str): array {
            $variants = [$str];

            // Strip diacritics / tashkeel
            $noTashkeel = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $str);
            if ($noTashkeel !== $str) {
                $variants[] = $noTashkeel;
            }

            // Normalize Alif forms (أ, إ, آ, ٱ -> ا)
            $alifNorm = preg_replace('/[إأآٱ]/u', 'ا', $noTashkeel);
            if ($alifNorm !== $noTashkeel) {
                $variants[] = $alifNorm;
            }

            // Normalize Taa Marbuta & Haa (ة <-> ه)
            if (str_contains($noTashkeel, 'ة')) {
                $variants[] = str_replace('ة', 'ه', $noTashkeel);
            } elseif (str_contains($noTashkeel, 'ه')) {
                $variants[] = str_replace('ه', 'ة', $noTashkeel);
            }

            // Normalize Yaa & Alif Maqsura (ى <-> ي)
            if (str_contains($noTashkeel, 'ى')) {
                $variants[] = str_replace('ى', 'ي', $noTashkeel);
            } elseif (str_contains($noTashkeel, 'ي')) {
                $variants[] = str_replace('ي', 'ى', $noTashkeel);
            }

            return array_unique($variants);
        };

        // Gather all phrase variants
        $allPhrases = [];
        foreach ($phrases as $phrase) {
            $allPhrases[] = $phrase;
            if (preg_match('/[\x{0600}-\x{06FF}]/u', $phrase)) {
                $allPhrases = array_merge($allPhrases, $normalizeArabic($phrase));
            }
        }
        $allPhrases = array_values(array_unique(array_filter($allPhrases)));

        return $builder->where(function (Builder $query) use ($allPhrases, $search) {
            // Direct code match
            $query->where('code', 'like', "%{$search}%");

            // Match full phrases
            foreach ($allPhrases as $phrase) {
                $query->orWhere('name', 'like', "%{$phrase}%")
                    ->orWhereHas('translations', function (Builder $tQuery) use ($phrase) {
                        $tQuery->where('name', 'like', "%{$phrase}%");
                    });
            }

            // Multi-word matching: if phrase has multiple words, match all words
            foreach ($allPhrases as $phrase) {
                $words = array_values(array_filter(preg_split('/\s+/u', $phrase), fn($w) => mb_strlen($w) >= 2));
                if (count($words) > 1) {
                    $query->orWhere(function (Builder $sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where(function (Builder $wQuery) use ($word) {
                                $wQuery->where('name', 'like', "%{$word}%")
                                    ->orWhere('code', 'like', "%{$word}%")
                                    ->orWhereHas('translations', function (Builder $tQuery) use ($word) {
                                        $tQuery->where('name', 'like', "%{$word}%");
                                    });
                            });
                        }
                    });
                }
            }
        });
    }
}
