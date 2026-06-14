<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutSection;
use App\Models\CategoryTab;
use App\Models\HomePage;
use App\Models\OurFact;
use App\Models\OurFactsSection;
use App\Models\OurPromise;
use App\Models\PortfolioItem;
use App\Models\PortfolioSection;
use App\Models\ProcessStep;
use App\Models\QuoteSection;
use App\Models\Review;
use App\Models\ReviewsSection;
use App\Models\ServiceCard;
use App\Models\ServiceSection;
use App\Models\WhatWeCreateSection;
use App\Models\WhatWeCreateTab;
use App\Models\WhyChooseUsSection;
use App\Models\WhyChooseUsTab;
use Illuminate\Support\Facades\Cache;

class PublicHomePayloadController extends Controller
{
    public function show()
    {
        $payload = Cache::remember('public_home_payload_v1', 60, function () {
            $categoryTabs = CategoryTab::orderBy('order', 'asc')->get();
            $whatWeCreateTabs = WhatWeCreateTab::with('categoryTab')->orderBy('order', 'asc')->get();

            return [
                'home_page' => $this->homePagePayload(),
                'about_section' => AboutSection::first(),
                'service' => [
                    'section' => ServiceSection::first(),
                    'cards' => ServiceCard::orderBy('order', 'asc')->get()->map(fn ($card) => [
                        'id' => $card->id,
                        'subtitle' => $card->subtitle,
                        'description' => $card->description,
                        'image' => $card->image_url,
                        'order' => $card->order,
                    ])->values(),
                ],
                'what_we_create' => [
                    'section' => WhatWeCreateSection::first(),
                    'categories' => $categoryTabs->map(fn ($tab) => [
                        'id' => $tab->id,
                        'category_name' => $tab->category_name,
                        'order' => $tab->order,
                    ])->values(),
                    'tabsMap' => $whatWeCreateTabs
                        ->groupBy('category_tab_id')
                        ->map(fn ($tabs) => $tabs->map(fn ($tab) => $this->whatWeCreateTabPayload($tab))->values())
                        ->all(),
                ],
                'why_choose_us' => [
                    'section' => WhyChooseUsSection::first(),
                    'tabs' => WhyChooseUsTab::orderBy('order', 'asc')->get(),
                ],
                'our_facts' => [
                    'section' => OurFactsSection::first(),
                    'facts' => OurFact::orderBy('order', 'asc')->get(),
                    'promise' => OurPromise::first(),
                    'processSteps' => ProcessStep::orderBy('order', 'asc')->get(),
                ],
                'testimonials' => [
                    'section' => ReviewsSection::first(),
                    'reviews' => Review::orderBy('order', 'asc')->get(),
                ],
                'portfolio' => [
                    'section' => PortfolioSection::first(),
                    'items' => PortfolioItem::orderBy('order', 'asc')->get(),
                ],
                'quote_section' => QuoteSection::first(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    private function homePagePayload(): ?array
    {
        $homePage = HomePage::first();
        if (!$homePage) return null;

        return [
            'id' => $homePage->id,
            'title' => $homePage->title,
            'buttonText' => $homePage->button_text,
            'buttonUrl' => $homePage->button_url,
            'button_text' => $homePage->button_text,
            'button_url' => $homePage->button_url,
            'secondaryButtonText' => $homePage->secondary_button_text,
            'secondaryButtonUrl' => $homePage->secondary_button_url,
            'secondary_button_text' => $homePage->secondary_button_text,
            'secondary_button_url' => $homePage->secondary_button_url,
            'top_label' => $homePage->top_label,
            'description' => $homePage->description,
            'trust_badge_1' => $homePage->trust_badge_1,
            'trust_badge_2' => $homePage->trust_badge_2,
            'trust_badge_3' => $homePage->trust_badge_3,
            'background_image' => $homePage->background_image_url,
            'secondary_image' => $homePage->secondary_image_url,
            'background_color' => $homePage->background_color,
            'accent_color' => $homePage->accent_color,
            'created_at' => $homePage->created_at,
            'updated_at' => $homePage->updated_at,
        ];
    }

    private function whatWeCreateTabPayload(WhatWeCreateTab $tab): array
    {
        return [
            'id' => $tab->id,
            'category_tab_id' => $tab->category_tab_id,
            'category_name' => $tab->categoryTab?->category_name,
            'tag_label' => $tab->tag_label,
            'main_heading' => $tab->main_heading,
            'description' => $tab->description,
            'features' => $tab->features ?? [],
            'button_text' => $tab->button_text,
            'image_1' => $tab->image_1_url,
            'image_2' => $tab->image_2_url,
            'image_3' => $tab->image_3_url,
            'order' => $tab->order,
        ];
    }
}
