<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Dashboard') | Sistem Futsal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%230f172a' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10' fill='%23ffffff'/%3E%3Cpolygon points='12 7.5 15.5 10 14 14 10 14 8.5 10' fill='%230f172a'/%3E%3Cpath d='M12 7.5V2M15.5 10L19.5 8.5M14 14L17.5 19.5M10 14L6.5 19.5M8.5 10L4.5 8.5'/%3E%3C/svg%3E">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full text-slate-900 antialiased font-sans" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Sidebar Include -->
        @include('layouts.partials.sidebar')

        <!-- Content Area -->
        <div class="flex-1 flex flex-col min-w-0 md:pl-60">
            
            <!-- Header Include -->
            @include('layouts.partials.header')

            <!-- Main Content -->
            <main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-[1600px] w-full mx-auto flex flex-col justify-between min-h-[calc(100vh-4rem)]">
                <div>
                    @yield('content')
                </div>
                <!-- Footer Copyright -->
                <footer class="mt-12 border-t border-slate-200 pt-6 text-center text-xs text-slate-400">
                    {{ \App\Models\Setting::getByKey('copyright', 'Copyright 2026 @ Asosiasi Futsal Provinsi Jawa Barat') }}
                </footer>
            </main>
        </div>
    </div>

    <!-- SweetAlert2 Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Session Toast Alerts
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: "{!! addslashes(session('success')) !!}"
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: "{!! addslashes(session('error')) !!}"
                });
            @endif

            // 2. Intercept and replace onsubmit="return confirm(...)" with SweetAlert2
            document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
                const onsubmitAttr = form.getAttribute('onsubmit');
                const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    const message = match[1];
                    form.removeAttribute('onsubmit');
                    
                    let confirmed = false;
                    form.addEventListener('submit', function(e) {
                        if (confirmed) return;
                        e.preventDefault();
                        
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: message,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                confirmed = true;
                                form.submit();
                            }
                        });
                    });
                }
            });

            // 3. Intercept and replace button onclick="return confirm(...)" with SweetAlert2
            document.querySelectorAll('button[onclick*="confirm("], a[onclick*="confirm("]').forEach(el => {
                const onclickAttr = el.getAttribute('onclick');
                const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
                if (match && match[1]) {
                    const message = match[1];
                    el.removeAttribute('onclick');
                    
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: message,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#2563eb',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Lanjutkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (el.tagName === 'A') {
                                    window.location.href = el.getAttribute('href');
                                } else if (el.type === 'submit') {
                                    const form = el.closest('form');
                                    if (form) {
                                        if (el.name && el.value) {
                                            const hidden = document.createElement('input');
                                            hidden.type = 'hidden';
                                            hidden.name = el.name;
                                            hidden.value = el.value;
                                            form.appendChild(hidden);
                                        }
                                        form.submit();
                                    }
                                }
                            }
                        });
                    });
                }
            });
        });
    </script>

    @livewireScripts
</body>
</html>
