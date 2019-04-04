<!DOCTYPE html>
<html>
<head>
    <title>123</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

</head>
<body>
    <div class="container tableContainer">
        <h1>list of salesman </h1>
        <a href="{{url('salesman.sales.test1')}}?is_del=1">is_del
        <a href="{{url('salesman.sales.test1')}}??is_del=0">no del
        <a href="{{url('salesman.sales.test1')}}??"> reset
        <hr>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>name</th>
                    <th>mobile</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{$sale->salens_name}}</td>
                    <td>{{$sale->mobile}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{$sales->links()}}
    </div>
</body>
</html>