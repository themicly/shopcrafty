{{--
    Google Analytics (GA4) + Facebook Pixel. Each fires independently once its
    own toggle is on AND an ID is set — same "flag + key" gate the cookie
    banner uses. Resolved via the theme:: fallback namespace, so this single
    file serves every theme (see ThemesServiceProvider).
--}}
@if (settings('tracking.ga_enabled') && ($gaId = settings('tracking.ga_measurement_id')))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', {{ Illuminate\Support\Js::from($gaId) }});
    </script>
@endif

@if (settings('tracking.fb_pixel_enabled') && ($fbPixelId = settings('tracking.fb_pixel_id')))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', {{ Illuminate\Support\Js::from($fbPixelId) }});
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" alt=""
            src="https://www.facebook.com/tr?id={{ urlencode($fbPixelId) }}&ev=PageView&noscript=1">
    </noscript>
@endif
