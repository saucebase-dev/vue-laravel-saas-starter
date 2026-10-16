## Coming Soon module

`modules/coming-soon` (namespace `Modules\ComingSoon`) owns the Coming Soon page,
site-wide settings, browser-page redirects, and the settings-manager banner.

- Application config owns route exceptions; never depend on another module.
- Keep `coming-soon.index` stable; its path comes from `coming-soon.path`.
- Only HTML GET/HEAD and Inertia visits redirect. Settings managers bypass them.

Activate `saucebase-coming-soon-development` before changing this module.
