<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha256-siyOpF/pBWUPgIcQi17TLBkjvNgNQArcmwJB8YvkAgg=" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha256-bqVeqGdJ7h/lYPq6xrPv/YGzMEb6dNxlfiTUHSgRCp8=" crossorigin="anonymous"></script>

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 13px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }

            .header {
                background-color: #444;
                color: #eee;
                text-align: center;
                padding: 5px;

            }

            .month {
                margin-top: 5px;
                margin-bottom: 5px;
                padding: 10px;
            }

            button, a {
                font-size: 20px !important;
            }
        </style>
    </head>
    <body>
        <div class="col-sm-12 header">
            <h1>Program wózka</h1>
        </div>
        <div class="container">
            <div class="col-sm-12 card month">
                <div class="row">
                <div class="col-sm-12 col-md-6" style="text-align: center;"><span style="font-size: 24px; font-weight: bold">Miesiąc</span></div>
                <div class="col-sm-12 col-md-6" style="text-align: center;"><input id="datepicker-val" class="datepicker" data-date-format="mm/dd/yyyy" value="{{$monthYearNow}}"></div>
                </div>
            </div>
            <div class="card">
                <a type="button" class="btn btn-primary" href="/index.php/download">Generuj druk</a>
            </div>
            <div class="card">
                <a type="button" class="btn btn-primary" href="/index.php/logout" style="color: white">Wyloguj</a>
            </div>
        </div>

    </body>
</html>
<script>
    $(".datepicker").datepicker( {
        format: "mm-yyyy",
        startView: "months",
        minViewMode: "months"
    });

    $('.datepicker').datepicker().on('changeDate', function (ev) {
        console.log($('#datepicker-val').val());
        document.cookie = "month_year=11-2019";
    });
</script>
