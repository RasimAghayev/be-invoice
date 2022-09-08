<!doctype html>

<html>

<head>

    @include('includes.head')

</head>

<body>

<div class="container-scroller">
    @include('includes.nav')
    <div class="container-fluid page-body-wrapper">

        @include('includes.sidebar')

        <div class="main-panel">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </div>

{{--        <footer class="footer">--}}
{{--            @include('includes.footer')--}}
{{--        </footer>--}}
    </div>

</div>
<!-- container-scroller -->
<!-- plugins:js -->
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js')}}"></script>
<!-- endinject -->
<!-- Plugin js for this page -->
<!-- End plugin js for this page -->
<!-- inject:js -->
<script src="{{ asset('assets/js/off-canvas.js')}}"></script>
<script src="{{ asset('assets/js/hoverable-collapse.js')}}"></script>
<script src="{{ asset('assets/js/misc.js')}}"></script>
<script src="{{ asset('assets/js/settings.js')}}"></script>
<script src="{{ asset('assets/js/todolist.js')}}"></script>
<!-- endinject -->
<!-- Custom js for this page -->
<!-- End custom js for this page -->
</body>
</body>

</html>
