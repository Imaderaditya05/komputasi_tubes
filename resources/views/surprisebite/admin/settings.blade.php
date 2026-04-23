<x-layouts.admin title="System Settings" active="settings">
    <div class="rounded-[24px] border-2 border-[#f3f4f6] bg-white p-6 shadow-[0_20px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] sm:p-8"
         style="background-image: linear-gradient(141.254deg, rgb(249, 250, 251) 0%, rgba(237, 233, 254, 0.35) 100%);">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-base font-bold text-[#4a5565] hover:text-[#7c3aed]">
            <span class="text-lg" aria-hidden="true">←</span>
            Back to Admin Dashboard
        </a>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700" aria-hidden="true"><x-sb.icon name="tag" class="h-7 w-7" /></span>
            <div>
                <h2 class="text-3xl font-black tracking-tight text-[#1e2939] sm:text-4xl">System Settings</h2>
                <p class="mt-1 text-base font-semibold text-[#4a5565]">Konfigurasi sistem SurpriseBite</p>
            </div>
        </div>

        <form method="post" action="{{ route('admin.settings.save') }}" class="mt-10 space-y-8" data-settings-live>
            @csrf

            <section class="rounded-3xl border-2 border-[#e5e7eb] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-[#1e2939]">General</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-bold text-slate-700">Site Name
                        <input name="site_name" data-setting-key="site_name" value="{{ $settings['site_name'] }}" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Support Email
                        <input name="support_email" data-setting-key="support_email" type="email" value="{{ $settings['support_email'] }}" required class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Support Phone
                        <input name="support_phone" data-setting-key="support_phone" value="{{ $settings['support_phone'] }}" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Language
                        <input name="language" data-setting-key="language" value="{{ $settings['language'] }}" placeholder="id" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                    <label class="block text-sm font-bold text-slate-700 sm:col-span-2">Timezone
                        <input name="timezone" data-setting-key="timezone" value="{{ $settings['timezone'] }}" placeholder="Asia/Jakarta" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                    </label>
                </div>
            </section>

            <section class="rounded-3xl border-2 border-[#e5e7eb] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-[#1e2939]">Notifications</h3>
                <div class="mt-4 space-y-4">
                    @foreach ([
                        'notify_system' => ['Enable System Notifications', 'Receive important system alerts'],
                        'notify_email' => ['Email Notifications', 'Send email notifications to users'],
                        'notify_sms' => ['SMS Notifications', 'Send SMS notifications to users'],
                    ] as $key => $meta)
                        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                            <div>
                                <p class="font-bold text-slate-900">{{ $meta[0] }}</p>
                                <p class="text-sm text-slate-600">{{ $meta[1] }}</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1" data-setting-key="{{ $key }}" class="peer sr-only" @checked($settings[$key])>
                                <span class="h-7 w-12 rounded-full bg-slate-300 peer-checked:bg-[#00a63e] after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border-2 border-[#e5e7eb] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-[#1e2939]">Business</h3>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-bold text-slate-700">Commission Rate (%)
                        <input name="commission_rate" data-setting-key="commission_rate" type="number" step="0.1" min="0" max="100" value="{{ $settings['commission_rate'] }}" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                        <span class="mt-1 block text-xs text-slate-500">Platform commission from each transaction</span>
                    </label>
                    <label class="block text-sm font-bold text-slate-700">Delivery Radius (km)
                        <input name="delivery_radius_km" data-setting-key="delivery_radius_km" type="number" step="0.1" min="1" max="500" value="{{ $settings['delivery_radius_km'] }}" class="mt-1 w-full rounded-xl border-2 border-slate-200 px-3 py-2 font-semibold" />
                        <span class="mt-1 block text-xs text-slate-500">Maximum delivery distance</span>
                    </label>
                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
                        <div>
                            <p class="font-bold text-slate-900">Auto-Approve Orders</p>
                            <p class="text-sm text-slate-600">Automatically approve incoming orders</p>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="hidden" name="auto_approve_orders" value="0">
                            <input type="checkbox" name="auto_approve_orders" value="1" data-setting-key="auto_approve_orders" class="peer sr-only" @checked($settings['auto_approve_orders'])>
                            <span class="h-7 w-12 rounded-full bg-slate-300 peer-checked:bg-[#00a63e] after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border-2 border-red-100 bg-red-50/50 p-6 shadow-sm">
                <h3 class="text-lg font-black text-red-900">System Maintenance</h3>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-red-200 bg-white px-4 py-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl" aria-hidden="true">⚠️</span>
                        <div>
                            <p class="font-bold text-red-900">Maintenance Mode</p>
                            <p class="text-sm text-red-800/90">Enable this to put the site in maintenance mode. Users won&apos;t be able to access the platform.</p>
                        </div>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input type="checkbox" name="maintenance_mode" value="1" data-setting-key="maintenance_mode" class="peer sr-only" @checked($settings['maintenance_mode'])>
                        <span class="h-7 w-12 rounded-full bg-slate-300 peer-checked:bg-red-600 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:transition peer-checked:after:translate-x-5"></span>
                    </label>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#7c3aed] to-[#6d28d9] px-8 py-4 text-lg font-black text-white shadow-lg hover:opacity-95 sm:w-auto">
                    <x-sb.icon name="package" class="h-6 w-6" />
                    Save All Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin>
