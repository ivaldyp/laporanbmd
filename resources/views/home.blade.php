@extends('layouts.masterhome')

@section('css')
    <!-- Bootstrap Core CSS -->
    <link href="{{ ('/bpadwebs/public/ample/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Menu CSS -->
    <link href="{{ ('/bpadwebs/public/ample/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css') }}" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{{ ('/bpadwebs/public/ample/css/animate.css') }}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ ('/bpadwebs/public/ample/css/style.css') }}" rel="stylesheet">
    <!-- color CSS -->
    <link href="{{ ('/bpadwebs/public/ample/css/colors/blue-dark.css') }}" id="theme" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
@endsection

<!-- /////////////////////////////////////////////////////////////// -->

@section('content')
    <div id="page-wrapper">
        <div class="container-fluid">
            <div class="row bg-title">
                <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                    <h4 class="page-title"><?php 
                                                $link = explode("/", url()->full());    
                                                echo ucwords($link[4]);
                                            ?> </h4> </div>
                <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
                    <ol class="breadcrumb">
                        <li>{{config('app.name')}}</li>
                        <?php 
                            $link = explode("/", url()->full());
                            if (count($link) == 5) {
                                ?> 
                                    <li class="active"> {{ ucwords($link[4]) }} </li>
                                <?php
                            } elseif (count($link) == 6) {
                                ?> 
                                    <li class="active"> {{ ucwords($link[4]) }} </li>
                                    <li class="active"> {{ ucwords($link[5]) }} </li>
                                <?php
                            } 
                        ?>   
                    </ol>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="white-box">
                        <h3 class="box-title">DASHBOARD</h3> 
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
        <footer class="footer text-center"> 2017 &copy; Ample Admin brought to you by themedesigner.in </footer>
    </div>
@endsection

<!-- /////////////////////////////////////////////////////////////// -->

@section('js')
    <script src="{{ ('/bpadwebs/public/ample/plugins/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.4.1.js"></script>
    <script src="{{ ('/bpadwebs/public/ample/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{{ ('/bpadwebs/public/ample/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') }}"></script>
    <!--slimscroll JavaScript -->
    <script src="{{ ('/bpadwebs/public/ample/js/jquery.slimscroll.js') }}"></script>
    <!--Wave Effects -->
    <script src="{{ ('/bpadwebs/public/ample/js/waves.js') }}"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{{ ('/bpadwebs/public/ample/js/custom.min.js') }}"></script>
    <!--Style Switcher -->
    <script src="{{ ('/bpadwebs/public/ample/plugins/bower_components/styleswitcher/jQuery.style.switcher.js') }}"></script>

    <script>
        $(document).ready(function() {
            var menus = <?php echo json_encode($sec_menu) ?>;
            var menus_child = <?php echo json_encode($sec_menu_child) ?>;
            var third_child = [];

            var fullpath = (window.location.href).split("/");
            var pathlen = fullpath.length;
            var geturl = "";
            
            $.each( menus, function( i, menu ) {    
                // $('.ulmenu').append( "<li> <a href='#' class='waves-effect'><i class=''></i> <span class='hide-menu'>menu</span></a> </li>");
                if (menu['child'] == 0) {
                    $('.ulmenu').append( "<li id='"+ menu['ids'] +"' class='urlnew' url='"+ menu.urlnew +"'> <a href='' class='waves-effect'><i class='fa fa-check fa-fw'></i> <span class='hide-menu'>"+ menu['desk'] +"</span></a></li>");
                } else  {
                    $('.ulmenu').append( 
                        '<li id="'+ menu['ids'] +'" class="urlnew" url="'+ menu.urlnew +'"> <a href="" class="waves-effect"><i class="fa fa-check fa-fw"></i> <span class="hide-menu">'+ menu['desk'] +'<span class="fa arrow"></span></span></a>'+
                        '<ul class="nav nav-second-level second'+ menu['ids'] +'">'
                        );
                }

                if (menu.urlnew == null) {
                    $('#' + menu['ids'] + ' a').attr('href', 'javascript:void(0)');
                } else {
                    $('#' + menu['ids'] + ' a').attr('href', menu.urlnew);
                }
            });
            if (menus_child.length > 0) {
                $.each (menus_child, function(i, child) {
                    if ($(".ulmenu li .nav-second-level").hasClass('second' + child['sao'])) {
                        if (child['child'] == 0) {
                            $('.second' + child['sao']).append( '<li id="'+ child['ids'] +'" class="urlnew" url="'+ child['urlnew'] +'"> <a href="" class="waves-effect"><i class="fa-fw"></i> <span class="hide-menu">'+ child['desk'] +'</span></a></li>');
                            $('#' + child['ids'] + ' a').attr('href', child.urlnew);
                        } else  {
                            $('.second' + child['sao']).append( 
                                '<li id="'+ child['ids'] +'" class="urlnew" url="'+ child['urlnew'] +'"> <a href="" class="waves-effect"><i class="fa-fw"></i> <span class="hide-menu">'+ child['desk'] +'<span class="fa arrow"></span></span></a>' +
                                '<ul class="nav nav-third-level third'+child['ids']+'">');
                            $('#' + child['ids'] + ' a').attr('href', child.urlnew);
                        }

                        if (child.urlnew == null) {
                            $('#' + child['ids'] + ' a').attr('href', 'javascript:void(0)');
                        } else {
                            $('#' + child['ids'] + ' a').attr('href', child.urlnew);
                        }

                    } else {
                        third_child.push(child['ids']);
                    }
                    
                });
                if (third_child.length > 0) {
                    $.each (menus_child, function(i, child) {
                        if ($.inArray(child['ids'], third_child) >= 0) {
                            $('.third' + child['sao']).append( '<li id="'+ child['ids'] +'" class="urlnew" url="'+ child['urlnew'] +'"> <a href="" class="waves-effect"><i class="fa-fw"></i> <span class="hide-menu">'+ child['desk'] +'</span></a>');
                            $('#' + child['ids'] + ' a').attr('href', child.urlnew);
                        }

                        if (child.urlnew == null) {
                            $('#' + child['ids'] + ' a').attr('href', 'javascript:void(0)');
                        } else {
                            $('#' + child['ids'] + ' a').attr('href', child.urlnew);
                        }
                    });
                }   
            }
            // $(".urlnew").click(function(e) 
            // { 
            //     alert($(this).attr('id'));
            //     var url = $(this).attr('url');
            //     var fullpath = window.location.href;
            // });
        });
    </script>
@endsection
