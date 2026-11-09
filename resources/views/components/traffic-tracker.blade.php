@if(!\Illuminate\Support\Facades\Auth::check() || \Illuminate\Support\Facades\Auth::user()->role !== 'admin')
<!-- Real-Time Traffic Tracker Engine -->
<script>
(function() {
    try {
        function getCookie(name) {
            const v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return v ? v[2] : null;
        }

        function setCookie(name, value, days) {
            const d = new Date();
            d.setTime(d.getTime() + 24*60*60*1000*days);
            document.cookie = name + "=" + value + ";path=/;SameSite=Lax;expires=" + d.toGMTString();
        }

        // Generate or retrieve visitor session UUID
        let sid = getCookie('_va_sid');
        if (!sid) {
            sid = localStorage.getItem('_va_sid');
        }
        if (!sid) {
            sid = 'va_' + Math.random().toString(36).substring(2, 15) + '_' + Date.now();
        }
        setCookie('_va_sid', sid, 1);
        try { localStorage.setItem('_va_sid', sid); } catch(e){}

        const PING_URL = '{{ route('traffic.ping') }}';
        const BEACON_URL = '{{ route('traffic.beacon') }}';
        const currentUrl = window.location.href;
        const pageTitle = document.title || '';
        const startTime = Date.now();
        let dwellSeconds = 0;
        let isIdle = false;

        function sendPing(action) {
            dwellSeconds = Math.round((Date.now() - startTime) / 1000);
            const payload = {
                session_id: sid,
                url: currentUrl,
                title: pageTitle,
                dwell_seconds: dwellSeconds,
                action: action || (document.hidden ? 'idle' : 'heartbeat'),
                screen: (window.screen.width || 0) + 'x' + (window.screen.height || 0)
            };

            if (navigator.sendBeacon && action === 'exit') {
                const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
                navigator.sendBeacon(BEACON_URL, blob);
            } else {
                fetch(PING_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload),
                    keepalive: action === 'exit'
                }).catch(function(){});
            }
        }

        // Send initial ping immediately to sync page title and active state
        setTimeout(function() { sendPing('enter'); }, 300);

        // Heartbeat interval every 15 seconds
        const heartbeatTimer = setInterval(function() {
            if (!document.hidden) {
                sendPing('heartbeat');
            }
        }, 15000);

        // Visibility change (tab switch / minimize)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                sendPing('idle');
            } else {
                sendPing('active');
            }
        });

        // Tab or window close / navigation exit
        window.addEventListener('beforeunload', function() {
            sendPing('exit');
        });

        window.addEventListener('pagehide', function() {
            sendPing('exit');
        });
    } catch(err) {
        // Tracker never breaks host page
    }
})();
</script>
@endif
