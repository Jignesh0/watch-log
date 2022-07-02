<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="//cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
    <div class="right_col" role="main">
        <div class="">
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <br><br>
                        @csrf
                        <div class="x_panel">
                        <div class="x_content">
                            <label>
                                <select name="date_from" id="date_from" class="form-control select2Common">
                                @isset($data['available_log_dates'])
                                    @foreach ($data['available_log_dates'] as $dates)
                                    <option value="{{$dates}}">{{$dates}}</option>
                                    @endforeach
                                @endisset
                                </select>
                            </label>&nbsp;&nbsp;&nbsp;
                            <label>
                                <select name="type" id="type" class="form-control select2Common" autocomplete="off">
                                <option value="">ALL</option>
                                <option value="INFO">INFO</option>
                                <option value="ERROR">ERROR</option>
                                <option value="WARNING">WARNING</option>
                                <option value="ALERT">ALERT</option>
                                <option value="DEBUG">DEBUG</option>
                                <option value="EMERGENCY">EMERGENCY</option>
                                </select>
                            </label>&nbsp;&nbsp;&nbsp;
                                <input type="submit" name="search" class="btn-search btn btn-primary" value="SEARCH">
                        </div>
                        <br>    <br>
                         <div class="row">
                            <div class="col-12">
                                <div class="card card-primary">
                                    <div class="card-body">
                                        <table class="table table-bordered responsive-table" id="posts_table">
                                            <thead>
                                                <tr class="headings" style='background-color:#3F5871;color:white'>
                                                    <th class="column-title">Type </th>
                                                    <th class="column-title">Context </th>
                                                    <th class="column-title">Date </th>
                                                    <th class="column-title">Description </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript"  src=" https://cdn.datatables.net/1.10.13/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
        log_list();
        $(".btn-search").click(function(){
                $('#posts_table').DataTable().destroy();
                log_list();
        });

        function log_list() {
            var table = $('#posts_table').DataTable({
                "serverSide": true,
                "processing": true,
                // "stateSave": true,
                "scrollY": "600px",
                "lengthMenu": [50, 100, 250,400],
                "ajax": {
                    "url": "{{ route('get-log-list') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data":{
                        date_from : $("#date_from").val(),
                        type : $('#type').val(),
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                },
                "columns": [{
                        "data": "level",
                        "orderable": false
                    },
                    {
                        "data": "context",
                        "orderable": false
                    },
                    {
                        "data": "date",
                        "orderable": false
                    },
                    {
                        "data": "description",
                        "orderable": false
                    },
                ]
            });
        }
    });
    </script>
</body>
