@php
    // Everything that shapes the website front lives together here.
    $nav = [
        ['label' => 'Design', 'items' => [
            ['route' => 'admin.themes.index', 'label' => 'Themes', 'desc' => 'Pick the active theme'],
            ['route' => 'admin.themes.customize', 'label' => 'Customize', 'desc' => 'Colors, fonts & layout'],
            ['route' => 'admin.themes.text', 'label' => 'Store text', 'desc' => 'Announcement, footer & page wording'],
            ['route' => 'admin.themes.sections', 'label' => 'Homepage', 'desc' => 'Arrange homepage sections'],
            ['route' => 'admin.banners.index', 'label' => 'Banners', 'desc' => 'Slider & promo strips'],
        ]],
        ['label' => 'Configuration', 'items' => [
            ['route' => 'admin.themes.settings', 'label' => 'Site settings', 'desc' => 'Wishlist, compare, reviews, newsletter', 'gate' => 'manage-config'],
        ]],
    ];
@endphp

<x-admin.section-shell title="Website" subtitle="How your online store looks and what shoppers can use." :nav="$nav">
    {{ $slot }}
</x-admin.section-shell>
